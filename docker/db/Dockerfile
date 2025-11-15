FROM postgres

ENV POSTGRES_PASSWORD nawrocky
ENV POSTGRES_DB nawrockytasks
ENV POSTGRES_USER nawrocky

COPY schema.sql /docker-entrypoint-initdb.d/
