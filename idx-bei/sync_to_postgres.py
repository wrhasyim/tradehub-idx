import os
import duckdb
import psycopg2
from psycopg2.extras import execute_values
from dotenv import load_dotenv

# Memuat konfigurasi dari file .env utama Laravel
load_dotenv("../.env")

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_DATABASE", "tradehub_db")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "")

def sync_parquet_to_postgres():
    print("🚀 Membaca data timeseries lengkap dari Parquet menggunakan DuckDB...")
    
    con = duckdb.connect()
    parquet_path = "data/timeseries/stock_summary/**/*.parquet"
    
    try:
        cols_df = con.execute(f"DESCRIBE SELECT * FROM read_parquet('{parquet_path}', union_by_name=true)").fetchdf()
        available_cols = cols_df['column_name'].tolist()
        print(f"📋 Kolom terdeteksi: {len(available_cols)} kolom tersedia.")
        
        # Pemetaan dinamis kolom Parquet IDX
        code_col = next((c for c in available_cols if c.lower() in ['stockcode', 'code', 'symbol']), 'StockCode')
        date_col = next((c for c in available_cols if c.lower() in ['date', 'trade_date']), 'Date')
        open_col = next((c for c in available_cols if c.lower() in ['openprice', 'open']), 'OpenPrice')
        high_col = next((c for c in available_cols if c.lower() == 'high'), 'High')
        low_col = next((c for c in available_cols if c.lower() == 'low'), 'Low')
        close_col = next((c for c in available_cols if c.lower() == 'close'), 'Close')
        vol_col = next((c for c in available_cols if c.lower() == 'volume'), 'Volume')
        change_col = next((c for c in available_cols if c.lower() in ['change', 'persen', 'percentage']), 'Change')
        val_col = next((c for c in available_cols if c.lower() == 'value'), 'Value')
        freq_col = next((c for c in available_cols if c.lower() == 'frequency'), 'Frequency')
        f_buy_col = next((c for c in available_cols if c.lower() == 'foreignbuy'), 'ForeignBuy')
        f_sell_col = next((c for c in available_cols if c.lower() == 'foreignsell'), 'ForeignSell')

        query = f"""
            SELECT 
                CAST("{code_col}" AS VARCHAR) AS stock_code,
                CAST("{date_col}" AS DATE) AS trade_date,
                CAST("{open_col}" AS DECIMAL(12,2)) AS open,
                CAST("{high_col}" AS DECIMAL(12,2)) AS high,
                CAST("{low_col}" AS DECIMAL(12,2)) AS low,
                CAST("{close_col}" AS DECIMAL(12,2)) AS close,
                CAST("{vol_col}" AS BIGINT) AS volume,
                CAST("{change_col}" AS DECIMAL(8,2)) AS change,
                CAST("{val_col}" AS DECIMAL(20,2)) AS value,
                CAST("{freq_col}" AS BIGINT) AS frequency,
                CAST("{f_buy_col}" AS DECIMAL(20,2)) AS foreign_buy,
                CAST("{f_sell_col}" AS DECIMAL(20,2)) AS foreign_sell
            FROM read_parquet('{parquet_path}', union_by_name=true)
            WHERE "{close_col}" > 0
        """
        
        df = con.execute(query).fetchdf()
        
        if df.empty:
            print("⚠️ Tidak ada data valid di Parquet.")
            return

        print(f"📊 Memproses {len(df)} baris data untuk dikirim ke PostgreSQL...")

        pg_conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        pg_cursor = pg_conn.cursor()

        records = [tuple(x) for x in df.to_numpy()]
        
        execute_values(
            pg_cursor,
            """
            INSERT INTO stock_prices (
                stock_code, trade_date, open, high, low, close, volume, 
                change, value, frequency, foreign_buy, foreign_sell, created_at, updated_at
            )
            VALUES %s
            ON CONFLICT (stock_code, trade_date) 
            DO UPDATE SET 
                open = EXCLUDED.open,
                high = EXCLUDED.high,
                low = EXCLUDED.low,
                close = EXCLUDED.close,
                volume = EXCLUDED.volume,
                change = EXCLUDED.change,
                value = EXCLUDED.value,
                frequency = EXCLUDED.frequency,
                foreign_buy = EXCLUDED.foreign_buy,
                foreign_sell = EXCLUDED.foreign_sell,
                updated_at = NOW()
            """,
            [(
                r[0], str(r[1]), float(r[2]), float(r[3]), float(r[4]), float(r[5]), int(r[6]),
                float(r[7]) if r[7] is not None else 0.0,
                float(r[8]) if r[8] is not None else 0.0,
                int(r[9]) if r[9] is not None else 0,
                float(r[10]) if r[10] is not None else 0.0,
                float(r[11]) if r[11] is not None else 0.0,
                'now', 'now'
            ) for r in records]
        )

        pg_conn.commit()
        pg_cursor.close()
        pg_conn.close()
        print("✅ Seluruh data lengkap (OHLCV, Asing, Value, Frequency) berhasil disinkronkan ke PostgreSQL!")

    except Exception as e:
        print(f"❌ Terjadi kesalahan saat sinkronisasi: {e}")

if __name__ == "__main__":
    sync_parquet_to_postgres()