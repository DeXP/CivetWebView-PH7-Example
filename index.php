<?php

function fs_get_roots() {
    static $roots = null;
    if ($roots === null) {
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            $driveLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < strlen($driveLetters); $i++) {
                $curDrive = $driveLetters[$i].':\\';
                if (is_dir($curDrive)) {
                    $roots[] = $curDrive;
                }
            }
        } else {
            foreach (file('/etc/fstab') as $line) {
                if ($line[0] != '#') {
                    $line = str_replace('\t', ' ', $line);
                    do $line = str_replace('  ', ' ', $line, $count); while($count);
                    $rows = explode(' ', $line);
                    if (count($rows) && is_dir($rows[1])) {
                        $roots[] = $rows[1];
                    }
                }
            }
        }      
    }
    return $roots;
}

function human_filesize($bytes, $dec = 1) {
    $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

function interpolate_colors($color1, $color2, $factor) {
    $res = '#';
    for ($i = 0; $i < count($color1); $i++) {
        $res .= sprintf("%02X", round($color1[$i] + ($factor * ($color2[$i] - $color1[$i]))));
    }
    return $res;
}

$systemDrives = fs_get_roots();

$emptyColor = array(181, 237, 19);
$fullColor = array(197, 49, 19);
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/css/progressbar.css" />
    </head>
<body>

    <main role="main" class="container">
        <h2 data-ru="Системные диски" data-cz="Systémové disky">System Drives</h2>
        <? foreach ($systemDrives as $Drive) {
            $totalSize = disk_total_space($Drive);
            $freeSize = disk_free_space($Drive);
            $usedSize = $totalSize - $freeSize;
            $usedFactor = ($totalSize > 0) ? $usedSize / $totalSize : 0;
            $usedPercent = 100 * $usedFactor;
        ?>
            <h3 class="progress-title"><?=$Drive;?> - <?=human_filesize($freeSize);?> <span data-ru="свободно из" data-cz="volno ze">free of</span> <?=human_filesize($totalSize);?></h3>
            <div class="progress">
                <div class="progress-bar" style="width:<?=$usedPercent;?>%; background:<?=interpolate_colors($emptyColor, $fullColor, $usedFactor);?>">
                    <div class="progress-value"><?=(int)$usedPercent;?>%</div>
                </div>
            </div>
        <? } ?>
    </main><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/js/jquery-3.2.1.slim.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <!-- Actual application code -->
    <script type="text/javascript">
        $(document).ready(function() {
            var userLang = (navigator.language || navigator.userLanguage)?.substring(0, 2)?.toLowerCase(); 
            $('[data-' + userLang + ']').each(function(element) {
                var localized = $(this).data(userLang);
                $(this).text(localized);
            });
        });
    </script>
</body>
</html>