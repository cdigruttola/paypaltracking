rm -rf tmp
mkdir -p tmp/paypaltracking
cp -R classes tmp/paypaltracking
cp -R config tmp/paypaltracking
cp -R docs tmp/paypaltracking
cp -R override tmp/paypaltracking
cp -R sql tmp/paypaltracking
cp -R src tmp/paypaltracking
cp -R translations tmp/paypaltracking
cp -R views tmp/paypaltracking
cp -R upgrade tmp/paypaltracking
cp -R vendor tmp/paypaltracking
cp -R index.php tmp/paypaltracking
cp -R logo.png tmp/paypaltracking
cp -R paypaltracking.php tmp/paypaltracking
cp -R config.xml tmp/paypaltracking
cp -R LICENSE tmp/paypaltracking
cp -R README.md tmp/paypaltracking
cd tmp && find . -name ".DS_Store" -delete
zip -r paypaltracking.zip . -x ".*" -x "__MACOSX"
