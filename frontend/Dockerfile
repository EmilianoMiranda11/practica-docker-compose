FROM nginx:alpine

# Copiar la configuración personalizada de Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copiar los archivos fuente del frontend al directorio web de Nginx
COPY src/ /usr/share/nginx/html
