const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? 
            walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

function mapUrlToRouteCall(url) {
    if (url === '/dashboard') return "route('dashboard')";
    if (url === '/marketplace') return "route('marketplace.index')";
    if (url === '/carrito') return "route('marketplace.cart')";
    
    let parts = url.substring(1).split('/');
    let resource = parts[0];
    
    if (parts.length === 1) {
        return `route('${resource}.index')`;
    } else if (parts.length === 2 && parts[1] === 'create') {
        return `route('${resource}.create')`;
    }
    return null;
}

let modifiedFiles = 0;

walkDir('resources/js', function(filePath) {
    if (!filePath.endsWith('.vue')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let newContent = content;

    // Fix object properties: href: '/path' -> href: route('path')
    newContent = newContent.replace(/href:\s*'(\/[^']+)'/g, (match, url) => {
        let mapped = mapUrlToRouteCall(url);
        if (mapped) {
            return `href: ${mapped}`;
        }
        return match;
    });

    // Fix HTML attributes: href="/path" -> :href="route('path')"
    // Use negative lookbehind or just carefully check prefix
    newContent = newContent.replace(/([^\:])href="(\/[^"]+)"/g, (match, prefix, url) => {
        let mapped = mapUrlToRouteCall(url);
        if (mapped) {
            return `${prefix}:href="${mapped}"`;
        }
        return match;
    });

    if (content !== newContent) {
        fs.writeFileSync(filePath, newContent, 'utf8');
        modifiedFiles++;
        console.log(`Updated ${filePath}`);
    }
});

console.log(`Modified ${modifiedFiles} files.`);
