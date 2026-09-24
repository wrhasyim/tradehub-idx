import os
import glob
import pandas as pd
import psycopg2
from psycopg2.extras import execute_values
import hashlib

# Konfigurasi Koneksi PostgreSQL (tradehub_db)
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_PORT = os.getenv("DB_PORT", "5432")
DB_NAME = os.getenv("DB_NAME", "tradehub_db")
DB_USER = os.getenv("DB_USER", "postgres")
DB_PASSWORD = os.getenv("DB_PASSWORD", "tradehubidx")

def get_connection():
    return psycopg2.connect(
        host=DB_HOST,
        port=DB_PORT,
        database=DB_NAME,
        user=DB_USER,
        password=DB_PASSWORD
    )

def get_col(df, possible_names, default=0):
    """Helper aman untuk mengambil kolom berdasarkan beberapa alternatif nama"""
    for name in possible_names:
        if name in df.columns:
            return df[name]
    return pd.Series([default] * len(df))

def init_db():
    """Inisialisasi tabel relasional pasar dan sistem otorisasi VIP user"""
    conn = get_connection()
    cur = conn.cursor()
    
    # 1. Tabel Users
    cur.execute("""
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(150) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'regular',
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    """)
    
    # 2. Tabel Stock Summaries
    cur.execute("""
        CREATE TABLE IF NOT EXISTS stock_summaries (
            date DATE,
            code VARCHAR(10),
            open NUMERIC,
            high NUMERIC,
            low NUMERIC,
            close NUMERIC,
            volume BIGINT,
            value NUMERIC,
            freq INT,
            foreign_buy NUMERIC DEFAULT 0,
            foreign_sell NUMERIC DEFAULT 0,
            PRIMARY KEY (date, code)
        );
    """)

    # 3. Tabel Broker Summaries
    cur.execute("""
        CREATE TABLE IF NOT EXISTS broker_summaries (
            date DATE,
            code VARCHAR(10),
            broker VARCHAR(10),
            buy_volume BIGINT DEFAULT 0,
            sell_volume BIGINT DEFAULT 0,
            net_volume BIGINT DEFAULT 0,
            buy_value NUMERIC DEFAULT 0,
            sell_value NUMERIC DEFAULT 0,
            net_value NUMERIC DEFAULT 0,
            freq INT DEFAULT 0,
            PRIMARY KEY (date, code, broker)
        );
    """)
    
    # 4. Tabel Index Summaries
    cur.execute("""
        CREATE TABLE IF NOT EXISTS index_summaries (
            date DATE,
            code VARCHAR(20),
            open NUMERIC,
            high NUMERIC,
            low NUMERIC,
            close NUMERIC,
            volume BIGINT,
            value NUMERIC,
            freq INT,
            PRIMARY KEY (date, code)
        );
    """)
    
    conn.commit()
    
    # --- SEEDING ADMIN UTAMA: TOTORO & EJA ---
    admin_list = [
        ("Totoro", "totoro@tradehubidx.id", "admin", None),
        ("Eja", "eja@tradehubidx.id", "admin", None)
    ]
    
    default_password_hash = hashlib.sha256("tradehub2026".encode()).hexdigest()
    
    for name, email, role, expires in admin_list:
        cur.execute("SELECT id FROM users WHERE email = %s", (email,))
        existing = cur.fetchone()
        if not existing:
            cur.execute(
                "INSERT INTO users (name, email, password, role, expires_at) VALUES (%s, %s, %s, %s, %s)",
                (name, email, default_password_hash, role, expires)
            )
            print(f" [SEEDED] Admin berhasil dibuat: {name} ({email})")
        else:
            print(f" [INFO] Admin {name} ({email}) sudah terdaftar.")
            
    conn.commit()
    cur.close()
    conn.close()
    print(" Struktur database 'tradehub_db' berhasil diinisialisasi secara komplit!")

def sync_stock_summaries():
    """Sinkronisasi data Parquet stock_summary ke PostgreSQL dengan pengaman kolom"""
    files = glob.glob("data/timeseries/stock_summary/**/*.parquet", recursive=True)
    if not files:
        print(" [WARNING] File stock_summary tidak ditemukan.")
        return

    conn = get_connection()
    cur = conn.cursor()
    print(f" Memproses {len(files)} partisi file Stock Summary...")
    
    for fp in files:
        df = pd.read_parquet(fp)
        if df.empty:
            continue
            
        date_s = get_col(df, ['Date', 'date', 'DATE'])
        code_s = get_col(df, ['Code', 'code', 'STOCK_CODE', 'StockCode'], default='')
        
        df['CleanDate'] = pd.to_datetime(date_s).dt.date
        df['CleanCode'] = code_s.astype(str)
        
        df = df.drop_duplicates(subset=['CleanDate', 'CleanCode'])
            
        records = []
        for _, r in df.iterrows():
            records.append((
                r['CleanDate'],
                r['CleanCode'],
                float(r.get('Open', r.get('open', 0))),
                float(r.get('High', r.get('high', 0))),
                float(r.get('Low', r.get('low', 0))),
                float(r.get('Close', r.get('close', 0))),
                int(r.get('Volume', r.get('volume', 0))),
                float(r.get('Value', r.get('value', 0))),
                int(r.get('Freq', r.get('freq', 0))),
                float(r.get('ForeignBuy', r.get('foreign_buy', 0))),
                float(r.get('ForeignSell', r.get('foreign_sell', 0)))
            ))

        query = """
            INSERT INTO stock_summaries (date, code, open, high, low, close, volume, value, freq, foreign_buy, foreign_sell)
            VALUES %s
            ON CONFLICT (date, code) DO UPDATE SET
                open = EXCLUDED.open, high = EXCLUDED.high, low = EXCLUDED.low, close = EXCLUDED.close,
                volume = EXCLUDED.volume, value = EXCLUDED.value, freq = EXCLUDED.freq,
                foreign_buy = EXCLUDED.foreign_buy, foreign_sell = EXCLUDED.foreign_sell;
        """
        execute_values(cur, query, records)
        conn.commit()
        
    cur.close()
    conn.close()
    print(" Sinkronisasi stock_summaries SELESAI!")

def sync_broker_summaries():
    """Sinkronisasi data Parquet broker_summary ke PostgreSQL dengan pengaman kolom"""
    files = glob.glob("data/timeseries/broker_summary/**/*.parquet", recursive=True)
    if not files:
        print(" [WARNING] File broker_summary tidak ditemukan.")
        return

    conn = get_connection()
    cur = conn.cursor()
    print(f" Memproses {len(files)} partisi file Broker Summary...")
    
    for fp in files:
        df = pd.read_parquet(fp)
        if df.empty:
            continue
            
        date_s = get_col(df, ['Date', 'date', 'DATE'])
        code_s = get_col(df, ['Code', 'code', 'STOCK_CODE', 'StockCode'], default='')
        broker_s = get_col(df, ['BrokerCode', 'broker', 'Broker', 'BROKER_CODE'], default='')
        
        df['CleanDate'] = pd.to_datetime(date_s).dt.date
        df['CleanCode'] = code_s.astype(str)
        df['CleanBroker'] = broker_s.astype(str)
        
        df = df.drop_duplicates(subset=['CleanDate', 'CleanCode', 'CleanBroker'])
        
        buy_vol = get_col(df, ['BuyVolume', 'buy_volume', 'Volume'])
        sell_vol = get_col(df, ['SellVolume', 'sell_volume'])
        buy_val = get_col(df, ['BuyValue', 'buy_value', 'Value'])
        sell_val = get_col(df, ['SellValue', 'sell_value'])
        freq_s = get_col(df, ['Freq', 'freq'])
        
        records = []
        for idx, (_, r) in enumerate(df.iterrows()):
            bv = int(buy_vol.iloc[idx] if hasattr(buy_vol, 'iloc') else buy_vol)
            sv = int(sell_vol.iloc[idx] if hasattr(sell_vol, 'iloc') else sell_vol)
            bval = float(buy_val.iloc[idx] if hasattr(buy_val, 'iloc') else buy_val)
            sval = float(sell_val.iloc[idx] if hasattr(sell_val, 'iloc') else sell_val)
            fr = int(freq_s.iloc[idx] if hasattr(freq_s, 'iloc') else freq_s)
            
            records.append((
                r['CleanDate'],
                r['CleanCode'],
                r['CleanBroker'],
                bv, sv, (bv - sv),
                bval, sval, (bval - sval),
                fr
            ))

        query = """
            INSERT INTO broker_summaries (date, code, broker, buy_volume, sell_volume, net_volume, buy_value, sell_value, net_value, freq)
            VALUES %s
            ON CONFLICT (date, code, broker) DO UPDATE SET
                buy_volume = EXCLUDED.buy_volume,
                sell_volume = EXCLUDED.sell_volume,
                net_volume = EXCLUDED.net_volume,
                buy_value = EXCLUDED.buy_value,
                sell_value = EXCLUDED.sell_value,
                net_value = EXCLUDED.net_value,
                freq = EXCLUDED.freq;
        """
        execute_values(cur, query, records)
        conn.commit()
        
    cur.close()
    conn.close()
    print(" Sinkronisasi broker_summaries SELESAI!")

def sync_index_summaries():
    """Sinkronisasi data Parquet index_summary ke PostgreSQL dengan pengaman kolom"""
    files = glob.glob("data/timeseries/index_summary/**/*.parquet", recursive=True)
    if not files:
        print(" [WARNING] File index_summary tidak ditemukan.")
        return

    conn = get_connection()
    cur = conn.cursor()
    print(f" Memproses {len(files)} partisi file Index Summary...")
    
    for fp in files:
        df = pd.read_parquet(fp)
        if df.empty:
            continue
            
        date_s = get_col(df, ['Date', 'date', 'DATE'])
        code_s = get_col(df, ['Code', 'code', 'IndexCode', 'index_code', 'INDEX_CODE'], default='')
        
        df['CleanDate'] = pd.to_datetime(date_s).dt.date
        df['CleanCode'] = code_s.astype(str)
        
        df = df.drop_duplicates(subset=['CleanDate', 'CleanCode'])
            
        records = []
        for _, r in df.iterrows():
            records.append((
                r['CleanDate'],
                r['CleanCode'],
                float(r.get('Open', r.get('open', 0))),
                float(r.get('High', r.get('high', 0))),
                float(r.get('Low', r.get('low', 0))),
                float(r.get('Close', r.get('close', 0))),
                int(r.get('Volume', r.get('volume', 0))),
                float(r.get('Value', r.get('value', 0))),
                int(r.get('Freq', r.get('freq', 0)))
            ))

        query = """
            INSERT INTO index_summaries (date, code, open, high, low, close, volume, value, freq)
            VALUES %s
            ON CONFLICT (date, code) DO UPDATE SET
                open = EXCLUDED.open, high = EXCLUDED.high, low = EXCLUDED.low, close = EXCLUDED.close,
                volume = EXCLUDED.volume, value = EXCLUDED.value, freq = EXCLUDED.freq;
        """
        execute_values(cur, query, records)
        conn.commit()
        
    cur.close()
    conn.close()
    print(" Sinkronisasi index_summaries SELESAI!")

if __name__ == "__main__":
    print("=== MENJALANKAN SINKRONISASI MUTLAK KE TRADEHUB_DB ===")
    init_db()
    sync_stock_summaries()
    sync_broker_summaries()
    sync_index_summaries()
    print("=== SELURUH DATA PASAR DAN AKUN ADMIN/VIP BERHASIL DISINKRONKAN! ===")