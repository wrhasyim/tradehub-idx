import os
import duckdb
import psycopg2
from psycopg2.extras import execute_values
from dotenv import load_dotenv
import json
import pandas as pd

# Memuat konfigurasi dari file .env utama Laravel
load_dotenv("../.env")

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_DATABASE", "tradehub_db")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "tradehubidx")

def sync_parquet_to_postgres():
    print("🚀 Membaca data timeseries lengkap (Stock & Broker Summary) dari Parquet menggunakan DuckDB...")
    
    con = duckdb.connect()
    stock_parquet = "data/timeseries/stock_summary/**/*.parquet"
    broker_parquet = "data/timeseries/broker_summary/**/*.parquet"
    
    try:
        # 1. Ambil dan proses Stock Summary
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

        print(f"📊 Memproses {len(df_stock)} baris data stock summary...")

        # 2. Ambil dan rangkum Broker Summary (Broksum) secara aman via Pandas
        broksum_map = {}
        try:
            print("🔍 Membaca dan merangkum data Broker Summary...")
            df_broker = con.execute(f"SELECT * FROM read_parquet('{broker_parquet}', union_by_name=true)").fetchdf()
            
            if not df_broker.empty:
                # Normalisasi nama kolom ke lowercase
                df_broker.columns = [c.lower() for c in df_broker.columns]
                
                b_code = next((c for c in df_broker.columns if c in ['stockcode', 'code', 'symbol', 'stock_code']), None)
                b_date = next((c for c in df_broker.columns if c in ['date', 'trade_date', 'tradedate']), None)
                b_broker = next((c for c in df_broker.columns if c in ['brokercode', 'broker', 'broker_code']), None)
                b_buy_vol = next((c for c in df_broker.columns if c in ['buyvolume', 'buy_volume', 'buyvol', 'volume_buy']), None)
                b_buy_avg = next((c for c in df_broker.columns if c in ['buyaverageprice', 'buy_average_price', 'buyavg', 'avg_buy']), None)
                b_sell_vol = next((c for c in df_broker.columns if c in ['sellvolume', 'sell_volume', 'sellvol', 'volume_sell']), None)
                b_sell_avg = next((c for c in df_broker.columns if c in ['sellaverageprice', 'sell_average_price', 'sellavg', 'avg_sell']), None)

                if b_code and b_date and b_broker:
                    df_broker['tdate_str'] = pd.to_datetime(df_broker[b_date]).dt.strftime('%Y-%m-%d')
                    
                    for (scode, tdate), group in df_broker.groupby([b_code, 'tdate_str']):
                        buyers_list = []
                        sellers_list = []
                        
                        if b_buy_vol and b_buy_avg:
                            top_buyers = group[group[b_buy_vol] > 0].sort_values(by=b_buy_vol, ascending=False).head(5)
                            buyers_list = [
                                {"broker": str(row[b_broker]), "lot": int(row[b_buy_vol]), "avg": float(row[b_buy_avg])}
                                for _, row in top_buyers.iterrows()
                            ]

                        if b_sell_vol and b_sell_avg:
                            top_sellers = group[group[b_sell_vol] > 0].sort_values(by=b_sell_vol, ascending=False).head(5)
                            sellers_list = [
                                {"broker": str(row[b_broker]), "lot": int(row[b_sell_vol]), "avg": float(row[b_sell_avg])}
                                for _, row in top_sellers.iterrows()
                            ]

                        key = f"{str(scode).upper()}_{tdate}"
                        broksum_map[key] = {
                            "buyer": buyers_list,
                            "seller": sellers_list
                        }
                    print(f"✅ Berhasil merangkum data broksum untuk {len(broksum_map)} sesi perdagangan.")
                else:
                    print(f⚠️ Kolom penting pada broker_summary tidak lengkap. Kolom ditemukan: {list(df_broker.columns)}) # type: ignore
        except Exception as e:
            print(f"⚠️ Catatan: Gagal membaca broker_summary: {e}")

        # 3. Masukkan ke PostgreSQL
        pg_conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        pg_cursor = pg_conn.cursor()

        insert_data = []
        for _, r in df_stock.iterrows():
            scode = str(r['stock_code']).upper()
            tdate = str(r['trade_date'])
            lookup_key = f"{scode}_{tdate}"
            
            json_broksum = json.dumps(broksum_map.get(lookup_key)) if lookup_key in broksum_map else None

            insert_data.append((
                scode, 
                tdate, 
                float(r['open']), 
                float(r['high']), 
                float(r['low']), 
                float(r['close']), 
                int(r['volume']),
                float(r['change']) if r['change'] is not None else 0.0,
                float(r['value']) if r['value'] is not None else 0.0,
                int(r['frequency']) if r['frequency'] is not None else 0,
                float(r['foreign_buy']) if r['foreign_buy'] is not None else 0.0,
                float(r['foreign_sell']) if r['foreign_sell'] is not None else 0.0,
                json_broksum,
                'now', 
                'now'
            ))

        print("📦 Menyimpan data ke PostgreSQL (Upsert)...")
        execute_values(
            pg_cursor,
            """
            INSERT INTO stock_prices (
                stock_code, trade_date, open, high, low, close, volume, 
                change, value, frequency, foreign_buy, foreign_sell, broksum_data, created_at, updated_at
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
                broksum_data = EXCLUDED.broksum_data,
                updated_at = NOW()
            """,
            insert_data
        )

        pg_conn.commit()
        pg_cursor.close()
        pg_conn.close()
        print("🎉 Selesai! Data OHLCV, Foreign Flow, dan Broksum JSON berhasil disinkronkan ke PostgreSQL.")

    except Exception as e:
        print(f"❌ Terjadi kesalahan saat sinkronisasi: {e}")

if __name__ == "__main__":
    sync_parquet_to_postgres()