// Usage: node fetch_image.js "<search term>" "<target-filename.jpg>" [resultIndex]
// Searches Wikimedia Commons for a photo matching the term and downloads the
// raw file into src/storage/app/public/products/_tmp_<target>. Uses curl
// (via child_process) because Wikimedia's edge blocks Node's fetch client but
// allows curl. Run on the HOST, not inside the docker container (which is
// separately rate-limited by Wikimedia).
// Prints "OK <title> <url> <tmpPath>" or "FAIL <reason>" to stdout.

const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const UA = 'MyAppDevProductPhotoFetcher/1.0';
const PRODUCTS_DIR = path.join(__dirname, 'src', 'storage', 'app', 'public', 'products');

function curlJson(url) {
    const out = execFileSync('curl', ['-sL', '-A', UA, '--max-time', '20', url]);
    return JSON.parse(out.toString('utf8'));
}

function curlBinary(url) {
    return execFileSync('curl', ['-sL', '-A', UA, '--max-time', '30', url], { maxBuffer: 50 * 1024 * 1024 });
}

function main() {
    const [term, target, indexArg] = process.argv.slice(2);
    const index = indexArg ? parseInt(indexArg, 10) : 0;

    if (!term || !target) {
        console.error('Usage: node fetch_image.js <term> <target-filename.jpg> [index]');
        process.exit(1);
    }

    const searchUrl = 'https://commons.wikimedia.org/w/api.php?action=query&list=search&srsearch='
        + encodeURIComponent(term + ' filetype:bitmap')
        + '&srnamespace=6&format=json&srlimit=10';

    const searchRes = curlJson(searchUrl);
    const hits = searchRes?.query?.search ?? [];
    if (!hits.length || !hits[index]) {
        console.log(`FAIL no_search_results for '${term}'`);
        process.exit(1);
    }

    const title = hits[index].title;
    const infoUrl = 'https://commons.wikimedia.org/w/api.php?action=query&titles='
        + encodeURIComponent(title)
        + '&prop=imageinfo&iiprop=url|size|mime&format=json';

    const infoRes = curlJson(infoUrl);
    const pages = infoRes?.query?.pages ?? {};
    const page = Object.values(pages)[0];
    const imageinfo = page?.imageinfo?.[0];

    if (!imageinfo?.url) {
        console.log(`FAIL no_image_url for '${title}'`);
        process.exit(1);
    }

    const buf = curlBinary(imageinfo.url);
    if (!buf || buf.length < 3000) {
        console.log(`FAIL too_small ${imageinfo.url}`);
        process.exit(1);
    }

    const tmpPath = path.join(PRODUCTS_DIR, `_tmp_${target}`);
    fs.writeFileSync(tmpPath, buf);

    console.log(`OK ${title} ${imageinfo.url} ${tmpPath}`);
}

main();
