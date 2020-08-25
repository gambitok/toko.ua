<?php

$social_array=[
//    ["name"=>"twitter",     "link"=>"#",    "img"=>"tw.png"],
    ["name"=>"facebook", "link"=>"https://www.facebook.com/TOKOGROUP/", "img"=>"fb.png"],
    ["name"=>"instagram",   "link"=>"https://www.instagram.com/tokogroup/", "img"=>"in.png"],
//    ["name"=>"group",       "link"=>"#",    "img"=>"gr.png"],
//    ["name"=>"google+",     "link"=>"#",    "img"=>"g+.png"]
];

$dist="/images/";
foreach($social_array as $key=>$value): ?>
    <li>
        <a itemprop="sameAs" target="_blank" href="<?php echo($value["link"])?>">
            <img
                src="<?php echo $dist?>social/<?php echo($value["img"])?>"
                alt="<?php echo($value["name"])?>"
            >
        </a>
    </li>
<?php endforeach; ?>

