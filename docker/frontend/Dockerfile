FROM node:20-alpine

WORKDIR /app

# --- TỐI ƯU HÓA ---
COPY frontend/package*.json ./

# Dùng 'npm ci' thay vì 'npm install'
# Lưu ý: Cần phải có file package-lock.json thì lệnh này mới chạy
RUN npm ci

COPY frontend/ .

CMD ["npm", "run", "dev", "--", "--host"]