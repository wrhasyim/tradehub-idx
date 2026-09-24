import os
import glob
import duckdb
import psycopg2
from psycopg2.extras import execute_values
from dotenv import load_dotenv
import pandas as pd
import numpy as np

load_dotenv("../.env")

DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_DATABASE", "tradehub_db")
DB_USER = os.getenv("DB_USERNAME", "postgres")
DB_PASS = os.getenv("DB_PASSWORD", "tradehubidx")

# 🛡️ LAPIS 1: Pembersih Angka Absolut (Anti-Crash karena tipe data)
def safe_int(val):
    try:
        if pd.isna(val) or val is None or np.isinf(val): return 0
        return int(float(val))
    except:
        return 0

def safe_float(val):
    try:
        if pd.isna(val) or val is None or np.isinf(val): return 0.0
        return float(val)
    except:
        return 0.0

def sync_parquet_to_postgres():
    print("🚀 Memulai sinkronisasi Parquet ke PostgreSQL (Mode ANTI-BADAI)...")
    
    stock_parquet = "data/timeseries/stock_summary/**/*.parquet"
    broker_parquet = "data/timeseries/broker_summary/**/*.parquet"
    
    # 🛡️ LAPIS 2: Pengecekan Eksistensi File
    if not glob.glob(stock_parquet, recursive=True):
        print("❌ Kritis: Tidak ada file Parquet untuk Stock Summary ditemukan!")
        return
        
    con = duckdb.connect()
    
    try:
        # ==========================================
        # A. PROSES STOCK SUMMARY
        # ==========================================
        print("🔍 Membaca dan membersihkan Stock Summary...")
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

        df_stock = con.execute(f"""
            SELECT 
                CAST("{code_col}" AS VARCHAR) AS stock_code, CAST("{date_col}" AS DATE) AS trade_date,
                CAST("{open_col}" AS DECIMAL(12,2)) AS open, CAST("{high_col}" AS DECIMAL(12,2)) AS high,
                CAST("{low_col}" AS DECIMAL(12,2)) AS low, CAST("{close_col}" AS DECIMAL(12,2)) AS close,
                CAST("{vol_col}" AS BIGINT) AS volume, CAST("{change_col}" AS DECIMAL(8,2)) AS change,
                CAST("{val_col}" AS DECIMAL(20,2)) AS value, CAST("{freq_col}" AS BIGINT) AS frequency,
                CAST("{f_buy_col}" AS DECIMAL(20,2)) AS foreign_buy, CAST("{f_sell_col}" AS DECIMAL(20,2)) AS foreign_sell
            FROM read_parquet('{stock_parquet}', union_by_name=true) WHERE "{close_col}" > 0
        """).fetchdf().drop_duplicates(subset=['stock_code', 'trade_date'], keep='last')
        
        # Ekstraksi ke list tuple dengan pembersihan ketat
        stock_insert_data = []
        for _, r in df_stock.iterrows():
            scode = str(r['stock_code']).upper().strip()
            if len(scode) > 20: scode = scode[:20]
            if scode == '' or scode == 'NAN' or scode == 'NONE': continue
            
            stock_insert_data.append((
                scode, str(r['trade_date']), safe_float(r['open']), safe_float(r['high']), 
                safe_float(r['low']), safe_float(r['close']), safe_int(r['volume']),
                safe_float(r['change']), safe_float(r['value']), safe_int(r['frequency']),
                safe_float(r['foreign_buy']), safe_float(r['foreign_sell']), 'now', 'now'
            ))
        print(f"📊 Siap memasukkan {len(stock_insert_data)} data Stock Summary.")

        # ==========================================
        # B. PROSES BROKER SUMMARY 
        # ==========================================
        broker_records = []
        if glob.glob(broker_parquet, recursive=True):
            print("🔍 Membaca dan mengkalkulasi Net Broker Summary...")
            try:
                df_broker = con.execute(f"SELECT *, filename FROM read_parquet('{broker_parquet}', union_by_name=true, filename=true)").fetchdf()
                if not df_broker.empty:
                    df_broker.columns = [c.lower() for c in df_broker.columns]
                    
                    def extract_stock(path):
                        parts = path.replace('\\', '/').split('/')
                        return parts[parts.index('broker_summary') + 1].upper() if 'broker_summary' in parts else 'UNKNOWN'
                    
                    df_broker['extracted_code'] = df_broker['filename'].apply(extract_stock)
                    b_date = next((c for c in df_broker.columns if c in ['date', 'trade_date']), 'date')
                    b_firm = next((c for c in df_broker.columns if c in ['firmnamee', 'brokercode', 'broker']), 'firmnamee')
                    
                    b_buy_vol = next((c for c in df_broker.columns if c in ['buyvolume', 'buy_volume']), None)
                    b_sell_vol = next((c for c in df_broker.columns if c in ['sellvolume', 'sell_volume']), None)
                    b_buy_val = next((c for c in df_broker.columns if c in ['buyaverageprice', 'buyvalue', 'buy_value']), None)
                    b_sell_val = next((c for c in df_broker.columns if c in ['sellaverageprice', 'sellvalue', 'sell_value']), None)
                    b_vol = next((c for c in df_broker.columns if c in ['volume']), 'volume')
                    b_val = next((c for c in df_broker.columns if c in ['value']), 'value')
                    b_freq = next((c for c in df_broker.columns if c in ['frequency']), 'frequency')

                    df_broker['tdate_str'] = pd.to_datetime(df_broker[b_date], errors='coerce').dt.strftime('%Y-%m-%d')
                    df_broker = df_broker.dropna(subset=['tdate_str']) # Buang baris jika tanggal tidak valid
                    df_broker = df_broker.drop_duplicates(subset=['extracted_code', 'tdate_str', b_firm], keep='last')
                    
                    for _, row in df_broker.iterrows():
                        scode = str(row['extracted_code']).strip()
                        if scode == 'UNKNOWN' or len(scode) > 20 or scode == '': continue
                            
                        tdate = str(row['tdate_str'])
                        bcode = str(row[b_firm]).strip()[:150] # Truncate broker name
                        if bcode == '' or bcode == 'nan' or bcode == 'None': continue
                        
                        buy_v = safe_int(row[b_buy_vol]) if b_buy_vol else safe_int(row[b_vol])
                        sell_v = safe_int(row[b_sell_vol]) if b_sell_vol else 0
                        
                        buy_val = safe_float(row[b_buy_val]) if b_buy_val else safe_float(row[b_val])
                        sell_val = safe_float(row[b_sell_val]) if b_sell_val else 0.0
                        
                        freq = safe_int(row[b_freq])
                        
                        broker_records.append((
                            scode, tdate, bcode, buy_v, sell_v, (buy_v - sell_v), 
                            buy_val, sell_val, (buy_val - sell_val), freq, 'now', 'now'
                        ))
                print(f"✅ Siap memasukkan {len(broker_records)} detail Broker Summary.")
            except Exception as e:
                print(f"⚠️ Peringatan: Ada masalah saat memproses broker summary (Mungkin format tidak konsisten). Error: {e}")
        else:
            print("⚠️ File Parquet Broker Summary tidak ditemukan. Melewati proses broksum...")

        # ==========================================
        # C. KONEKSI & EKSEKUSI UPSERT (CHUNKED)
        # ==========================================
        print("📦 Menyambungkan ke PostgreSQL...")
        pg_conn = psycopg2.connect(host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS)
        
        # 🛡️ LAPIS 3: Fungsi Pemasok Blok (Chunking) untuk isolasi error
        def bulk_upsert_safe(query, data_list, table_name, chunk_size=5000):
            total_inserted = 0
            for i in range(0, len(data_list), chunk_size):
                chunk = data_list[i:i + chunk_size]
                try:
                    cursor = pg_conn.cursor()
                    execute_values(cursor, query, chunk)
                    pg_conn.commit()
                    cursor.close()
                    total_inserted += len(chunk)
                except Exception as e:
                    pg_conn.rollback() # Batalkan hanya blok yang error
                    print(f"❌ Gagal memasukkan blok {i} s/d {i + len(chunk)} ke tabel {table_name}. Error: {str(e)[:150]}...")
            print(f"✅ Berhasil menyimpan {total_inserted} / {len(data_list)} baris ke tabel {table_name}.")

        # Eksekusi Stock
        stock_query = """
            INSERT INTO stock_prices (
                stock_code, trade_date, open, high, low, close, volume, 
                change, value, frequency, foreign_buy, foreign_sell, created_at, updated_at
            ) VALUES %s
            ON CONFLICT (stock_code, trade_date) DO UPDATE SET 
                open = EXCLUDED.open, high = EXCLUDED.high, low = EXCLUDED.low, close = EXCLUDED.close,
                volume = EXCLUDED.volume, change = EXCLUDED.change, value = EXCLUDED.value,
                frequency = EXCLUDED.frequency, foreign_buy = EXCLUDED.foreign_buy,
                foreign_sell = EXCLUDED.foreign_sell, updated_at = NOW()
        """
        bulk_upsert_safe(stock_query, stock_insert_data, "stock_prices")

        # Eksekusi Broker
        if broker_records:
            broker_query = """
                INSERT INTO broker_summaries (
                    stock_code, trade_date, broker_code, buy_volume, sell_volume, net_volume,
                    buy_value, sell_value, net_value, frequency, created_at, updated_at
                ) VALUES %s
                ON CONFLICT (stock_code, trade_date, broker_code) DO UPDATE SET 
                    buy_volume = EXCLUDED.buy_volume, sell_volume = EXCLUDED.sell_volume,
                    net_volume = EXCLUDED.net_volume, buy_value = EXCLUDED.buy_value,
                    sell_value = EXCLUDED.sell_value, net_value = EXCLUDED.net_value,
                    frequency = EXCLUDED.frequency, updated_at = NOW()
            """
            bulk_upsert_safe(broker_query, broker_records, "broker_summaries")

        pg_conn.close()
        print("🎉 SINKRONISASI SELESAI! Struktur database baru sudah terisi penuh.")

    except Exception as e:
        print(f"❌ Terjadi kesalahan fatal pada struktur utama: {e}")

if __name__ == "__main__":
    sync_parquet_to_postgres()