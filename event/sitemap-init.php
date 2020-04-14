<?php

$list="<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 	http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">";

$r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD_STR` WHERE 1;"); $n=$db->num_rows($r);
for ($i=1;$i<=$n;$i++) {
    $TEX_LINK=$db->result($r,$i-1,"TEX_LINK");
    $list.="
<url>
    <loc>https://toko.ua/catalog/$TEX_LINK/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
</url>";
}

$list.="</urlset>";

$my_file = '1_test.txt';
$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
$data = $list;
fwrite($handle, $data);

