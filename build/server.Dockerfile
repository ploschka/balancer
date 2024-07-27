FROM ploshka/symfony-nginx:latest
WORKDIR /app
COPY ./ /app
# Try to not copy the files to see what going to happen then
