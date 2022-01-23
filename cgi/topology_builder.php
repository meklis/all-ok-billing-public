<?php

require __DIR__ . '/../envPHP/load.php';

//Get raw table
$raw = dbConnPDO()->query("SELECT DISTINCT  a.mac switch, f.switch switch_ip, f.port, f.mac
FROM `walker_fdb` f 
JOIN (SELECT distinct mac, ip FROM walkers_arps ) a on a.ip = f.switch
JOIN (SELECT distinct w.mac FROM walkers_arps w JOIN equipment e on e.ip = w.ip ) filt on filt.mac = f.mac 
WHERE stop_at is null 
and f.port != 0 ")->fetchAll();


//Structure data
$SRC_DST = [];
foreach ($raw as $d) {
    $SRC_DST[$d['switch']][$d['port']][] = $d['mac'];
}

$TOPOLOGY = [];
    RE_TOPOL:
    foreach ($SRC_DST as $srcDev => $srcPorts) {
        foreach ($srcPorts as $srcPort => $destDevs) {
            if (count($destDevs) == 1) {
                $destDev = array_values($destDevs)[0];
                \envPHP\classes\std::msg(json_encode(
                    [
                        'srcDev' => $srcDev,
                        'destDev' => $destDev,
                    ]
                ));
                $destPort = findUplinkPort($raw, $destDev, $srcDev);
                $TOPOLOGY[] = [
                    'src_dev' => $srcDev,
                    'src_port' => $srcPort,
                    'dest_dev' => $destDev,
                    'dest_port' => $destPort,
                ];
                \envPHP\classes\std::msg("Added to topology - {$srcDev}-{$srcPort} -> {$destDev}-{$destPort}");
                deleteDevFromAll($SRC_DST, $destDev);
                dbConnPDO()->prepare("
                   INSERT INTO walker_topology (src_mac, src_port, dest_mac, dest_port) VALUES (?,?,?,?)
                ")->execute([$srcDev, $srcPort, $destDev, $destPort]);
                goto RE_TOPOL;
            }
        }
    }
\envPHP\classes\std::msg("Count undefined devs = " . count($SRC_DST));



function findUplinkPort(&$raw, $srcDev, $destDev) {
    $filtered = array_filter($raw, function ($e) use ($srcDev, $destDev) {
       return $e['switch'] === $srcDev && $e['mac'] === $destDev;
    });
    if(count($filtered) == 1) {
        return array_values($filtered)[0]['port'];
    } elseif (count($filtered) == 0) {
        \envPHP\classes\std::msg("Not found uplink port for device {$destDev}");
        return -1;
    } else {
        return -2;
    }
}

function deleteDevFromAll(&$SRC_DST, $des) {
    \envPHP\classes\std::msg("Clear dev $des from all");
    foreach ($SRC_DST as $srcDev => $srcPorts) {
        foreach ($srcPorts as $srcPort => $destDevs) {
            foreach ($destDevs as $_id=>$destDev) {
                if($destDev==$des) {
                    unset($SRC_DST[$srcDev][$srcPort][$_id]);
                }
            }
            if(count($SRC_DST[$srcDev][$srcPort]) == 0) {
                unset($SRC_DST[$srcDev][$srcPort]);
            }
        }
        if(count($SRC_DST[$srcDev]) == 0) {
            unset($SRC_DST[$srcDev]);
        }
    }
}


