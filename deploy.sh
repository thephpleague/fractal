rm -rf output_prod;
sculpin install;
sculpin generate --env=prod;
echo "fractal.thephpleague.com" > ./output_prod/CNAME
cd output_prod && git init && git remote add origin https://github.com/thephpleague/fractal.git && git add -A && git commit -m "Publish" && git push --force origin HEAD:gh-pages && cd ..
rm -rf output_prod;
