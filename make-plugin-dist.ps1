$out="c:\tmp\fapi-member";
$inside = Join-Path $out "*";
Remove-Item -Path $inside -Force -Recurse;
&npx webpack;
Copy-Item -Path ./* -Destination $out -Recurse -Force;

## remove unwanted in dist zip
$to_remove="node_modules",".git","README.md",".gitignore","package.json","package-lock.json", "webpack.config.js","make-plugin-dist.ps1",
    "media/font/specimen_files","media/font/generator_config.txt","media/font/proxima_nova_font-demo.html",
    "media/fapi-member.scss","media/fapi-member-public.scss","media/fapi-user-profile.scss","media/colors.scss",
    "media/fapi-member.css.map","media/fapi-member-public.css.map","media/fapi-user-profile.css.map","media/colors.css.map",
    "media/colors.css","media/fapi.js";
foreach($name in $to_remove) {
    $a = Join-Path $out $name;
    Remove-Item -Path $a -Force -Recurse;
}

