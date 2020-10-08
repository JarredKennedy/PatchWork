const path = require('path');
const fs = require('fs');

var root = path.resolve(__dirname, '../');
var appDir = path.resolve(root, 'app');

fs.unlinkSync(`${appDir}/dist/app.asset.php`);