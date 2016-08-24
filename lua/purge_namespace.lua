local ns = ARGV[1]

for _,k in ipairs(redis.call('KEYS', ns..'*')) do
    redis.call('DEL', k)
end
