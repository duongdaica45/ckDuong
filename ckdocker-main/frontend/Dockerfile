# File: frontend/Dockerfile

FROM nginx:alpine

# Xóa file mặc định của Nginx
RUN rm -rf /usr/share/nginx/html/*

# *** SỬA ĐỔI: Sử dụng đường dẫn tương đối (frontend/index.html) ***
# Docker Context là thư mục gốc D:\dockerck
COPY frontend/index.html /usr/share/nginx/html/index.html

EXPOSE 80