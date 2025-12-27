#!/bin/sh

# 1. Chạy Ollama server dưới nền (&)
ollama serve &

# Lưu lại Process ID để quản lý
PID=$!

# 2. Đợi 5 giây để server khởi động xong
echo "⏳ Đang khởi động Ollama Server..."
sleep 5

# 3. Tự động tải model (Nếu có rồi nó sẽ bỏ qua rất nhanh)
echo "⬇️ Kiểm tra và tải model phi4-mini..."
ollama pull phi4-mini

echo "✅ Ollama đã sẵn sàng!"

# 4. Giữ container luôn chạy (chờ process PID)
wait $PID