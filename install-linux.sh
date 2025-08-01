#!/bin/sh

# Exit kalau ada error
set -e

echo "🔧 Updating package list..."
apk update

echo "📦 Installing system packages..."
apk add --no-cache python3 py3-pip py3-virtualenv

echo "💡 Setting up virtual environment..."
cd embedding_service
python3 -m venv myenv

echo "📦 Activating venv and installing Python requirements..."
. myenv/bin/activate
pip install --upgrade pip
pip install torch --index-url https://download.pytorch.org/whl/cpu
pip install -r requirements.txt

echo "🎉 Python setup complete."

