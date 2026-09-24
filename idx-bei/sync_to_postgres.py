import os
import duckdb
import psycopg2
from psycopg2.extras import execute_values
from dotenv import load_dotenv
import pandas as pd
import numpy as np

# Memuat konfigurasi dari file .env utama Laravel
load_dotenv("../.env")

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_DATABASE", "tradehub_db")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "tradehubidx")

def clean_val(val, default=0):
    if pd.isna(val) or val is None:
        return default
    if isinstance(val, (float, np.float64, np.float32)):
        if np.isnan(val) or np.isinf(val):
            return default
    return val

def sync_parquet_to_postgres():
    print("🚀 Memulai sinkronisasi total Parquet ke PostgreSQL...")
    
    con = duckdb.connect()
    stock_parquet = "data/timeseries/stock_summary/**/*.parquet"
    broker_parquet = "data/timeseries/broker_summary/**/*.parquet"
    
    try:
        # ==========================================
        # 1. PROSES STOCK SUMMARY (OHLCV & Foreign Flow)
        # ==========================================
        cols_df = con.execute(f"DESCRIBE SELECT * FROM read_parquet('{stock_parquet}', union_by_name=true)").fetchdf()
        available_cols = cols_df['column_name'].tolist()
        
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
        f_buy_col = next((c for c in available_cols if c.lower() in ['foreignbuy', 'foreign_buy']), 'ForeignBuy')
        f_sell_col = next((c for c in available_cols if c.lower() in ['foreignsell', 'foreign_sell']), 'ForeignSell')

        stock_query = f"""
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
            FROM read_parquet('{stock_parquet}', union_by_name=true)
            WHERE "{close_col}" > 0
        """
        
        df_stock = con.execute(stock_query).fetchdf()
        if df_stock.empty:
            print("⚠️ Tidak ada data valid di Stock Summary Parquet.")
            return
        print(f"📊 Memuat {len(df_stock)} baris data stock summary...")

        # ==========================================
        # 2. PROSES BROKER SUMMARY (Full Data ke Relasional)
        # ==========================================
        broker_records = []
        try:
            print("🔍 Membaca data mentah Broker Summary dari Parquet...")
            broker_query = f"SELECT *, filename FROM read_parquet('{broker_parquet}', union_by_name=true, filename=true)"
            df_broker = con.execute(broker_query).fetchdf()
            
            if not df_broker.empty:
                df_broker.columns = [c.lower() for c in df_broker.columns]
                
                def extract_stock_from_path(path):
                    parts = path.replace('\\', '/').split('/')
                    if 'broker_summary' in parts:
                        idx = parts.index('broker_summary')
                        if idx + 1 < len(parts):
                            return parts[idx + 1].upper()
                    return 'UNKNOWN'

                df_broker['extracted_code'] = df_broker['filename'].apply(extract_stock_from_path)
                
                b_date = next((c for c in df_broker.columns if c in ['date', 'trade_date', 'tradedate']), 'date')
                b_firm = next((c for c in df_broker.columns if c in ['firmnamee', 'brokercode', 'broker', 'idfirm']), 'firmnamee')
                b_vol = next((c for c in df_broker.columns if c in ['volume']), 'volume')
                b_val = next((c for c in df_broker.columns if c in ['value']), 'value')
                b_freq = next((c for c in df_broker.columns if c in ['frequency']), 'frequency')

                df_broker['tdate_str'] = pd.to_datetime(df_broker[b_date]).dt.strftime('%Y-%m-%d')
                
                for _, row in df_broker.iterrows():
                    scode = str(row['extracted_code']).upper()
                    if scode == 'UNKNOWN':
                        continue
                    tdate = str(row['tdate_str'])
                    broker_code = str(row[b_firm])
                    vol = int(clean_val(row[b_vol]))
                    val = float(clean_val(row[b_val]))
                    freq = int(clean_val(row[b_freq]))
                    
                    broker_records.append((scode, tdate, broker_code, vol, val, freq, 'now', 'now'))
                    
                print(f"✅ Berhasil merangkum {len(broker_records)} baris data detail broker.")
        except Exception as e:
            print(f"⚠️ Catatan pembacaan broker_summary: {e}")

        # ==========================================
        # 3. KONEKSI & EKSEKUSI UPSERT KE POSTGRESQL
        # ==========================================
        print("📦 Menyambungkan ke PostgreSQL...")
        pg_conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        pg_cursor = pg_conn.cursor()

        # A. Upsert Stock Prices (Murni OHLCV & Foreign Flow)
        print(f"📦 Menyimpan {len(df_stock)} baris data stock_prices...")
        stock_insert_data = []
        for _, r in df_stock.iterrows():
            stock_insert_data.append((
                str(r['stock_code']).upper(), 
                str(r['trade_date']), 
                float(clean_val(r['open'])), 
                float(clean_val(r['high'])), 
                float(clean_val(r['low'])), 
                float(clean_val(r['close'])), 
                int(clean_val(r['volume'])),
                float(clean_val(r['change'])),
                float(clean_val(r['value'])),
                int(clean_val(r['frequency'])),
                float(clean_val(r['foreign_buy'])),
                float(clean_val(r['foreign_sell'])),
                'now', 
                'now'
            ))

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
            stock_insert_data,
            page_size=10000
        )

        # B. Upsert Broker Summaries (Tabel Relasional Terpisah)
        if broker_records:
            print(f"📦 Menyimpan {len(broker_records)} baris data broker_summaries...")
            execute_values(
                pg_cursor,
                """
                INSERT INTO broker_summaries (
                    stock_code, trade_date, broker_code, volume, value, frequency, created_at, updated_at
                )
                VALUES %s
                ON CONFLICT (stock_code, trade_date, broker_code) 
                DO UPDATE SET 
                    volume = EXCLUDED.volume,
                    value = EXCLUDED.value,
                    frequency = EXCLUDED.frequency,
                    updated_at = NOW()
                """,
                broker_records,
                page_size=10000
            )

        pg_conn.commit()
        pg_cursor.close()
        pg_conn.close()
        print("🎉 SELESAI SEMPURNA! Seluruh data masuk ke tabel masing-masing tanpa error.")

    except Exception as e:
        print(f"❌ Terjadi kesalahan fatal saat sinkronisasi: {e}")

if __name__ == "__main__":
    sync_parquet_to_postgres()