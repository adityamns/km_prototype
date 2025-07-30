#!/bin/sh

# Jalankan Laravel server di background
php artisan serve --host=0.0.0.0 --port=8000 &

# Jalankan Uvicorn (FastAPI) di background
cd embedding_service
./myenv/bin/uvicorn main:app --host 0.0.0.0 --port=8180 &

# Tunggu semua proses selesai
wait

