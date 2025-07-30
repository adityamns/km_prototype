#!/bin/bash
set -e

echo "🔧 Updating package list..."
sudo apt update

echo "📦 Installing system packages..."
sudo apt install -y python3 python3-pip python3-venv

echo "💡 Setting up virtual environment..."
cd embedding_service
python3 -m venv myenv

echo "📦 Activating venv and installing Python requirements..."
. myenv/bin/activate
pip install --upgrade pip
pip install torch --index-url https://download.pytorch.org/whl/cpu
pip install -r requirements.txt

echo "🎉 Python setup complete."
