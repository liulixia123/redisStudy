#bin/bash 
msg=redis-cli bgrewriteaof
result = `redis-cli info persistence | grep aof_rewrite_in_progress | awk -F ':' '{print $2}' `
while [`echo $result eq "1"`]; do
	 sleep 1
	result = `redis-cli info persistence | grep aof_rewrite_in_progress | awk -F ':' '{print $2}' `
done
dataDir=`date +%Y%m%d%`
dataFile=`date +%H`
scpDir = /redis/data/140/aof/$dataDir

ssh root@192.168.169.150 "mkdir -p $scpDir/"
scp -p /redis/data/appendonly.aof root@192.168.169.150:$scpDir/$dataFile".aof"
#删除10天前的所有文件
find /tmp/* -type f -mtime +10 -exec rm {} \;
