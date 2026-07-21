// Usage: node fetch_image2.js "<search term>" "<target-filename.jpg>" [resultIndex]
// Same as fetch_image.js but sources from the Openverse API (openverse.org),
// which aggregates CC-licensed images from Flickr/etc and is not subject to
// Wikimedia's upload.wikimedia.org edge rate limit that blocked us mid-run.
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
        console.error('Usage: node fetch_image2.js <term> <target-filename.jpg> [index]');
        process.exit(1);
    }

    const searchUrl = 'https://api.openverse.org/v1/images/?q='
        + encodeURIComponent(term)
        + '&license_type=commercial,modification&page_size=10&mature=false';

    const searchRes = curlJson(searchUrl);
    const hits = searchRes?.results ?? [];
    if (!hits.length || !hits[index]) {
        console.log(`FAIL no_search_results for '${term}'`);
        process.exit(1);
    }

    const hit = hits[index];
    const imgUrl = hit.url;

    if (!imgUrl) {
        console.log(`FAIL no_image_url for '${hit.title}'`);
        process.exit(1);
    }

    const buf = curlBinary(imgUrl);
    if (!buf || buf.length < 3000) {
        console.log(`FAIL too_small ${imgUrl}`);
        process.exit(1);
    }

    const tmpPath = path.join(PRODUCTS_DIR, `_tmp_${target}`);
    fs.writeFileSync(tmpPath, buf);

    console.log(`OK ${hit.title} ${imgUrl} ${tmpPath}`);
}

main();
