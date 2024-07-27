FROM ploshka/symfony-app:latest
WORKDIR /app
COPY ./ /app
RUN /configure.sh

