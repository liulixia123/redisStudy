msg=redis-cli bgsave
result=`redis-cli info persistence | grep rdb_bgsave_in_progress | awk -F ':' '{print $2}'`
while [ `echo $result` -eq "1"];
do
        sleep 1
        result=`redis-cli info persistence | grep rdb_bgsave_in_progress | awk -F ':' '{print $2}'`
done

dataDir=`date +%Y%m%d%`
dataFile=`date +%H`
scpDir=/redis/data/140/rdb/$dataDir

ssh root@192.168.169.150 "mkdir -p $scpDir/"
scp -p /redis/data/dump.rdb root@192.168.169.150:$scpDir/$dataFile".rdb"