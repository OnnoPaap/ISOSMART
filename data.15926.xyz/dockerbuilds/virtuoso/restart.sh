rm /opt/database/virtuoso.lck
docker stop virtuoso
docker rm virtuoso

docker run \
    --name virtuoso \
    -d \
    --tty \
    --env DBA_PASSWORD=CVwAkNqI8 \
    --publish  8890:8890 \
    --volume /opt/database:/database \
    openlink/virtuoso-opensource-7:latest


