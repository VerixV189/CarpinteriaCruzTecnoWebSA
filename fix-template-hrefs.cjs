const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

function mapTemplateHref(urlString) {
    let inner = urlString.substring(1, urlString.length - 1);
    let match = inner.match(/^\/([a-z_]+)\/\$\{([^}]+)\}(?:\/([a-z_]+))?$/);
    if (match) {
        let resource = match[1];
        let id = match[2];
        let action = match[3];
        
        if (!action) {
            return `route('${resource}.show', ${id})`;
        } else if (action === 'edit') {
            return `route('${resource}.edit', ${id})`;
        }
    }
    return null;
}

let modifiedFiles = 0;

walkDir('resources/js', function(filePath) {
    if (!filePath.endsWith('.vue')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let newContent = content;

    // Pattern for :href="`/resource/${id}...`"
    let regex = /:href="(`\/[^`]+`)"/g;
    
    newContent = newContent.replace(regex, (match, fullUrl) => {
        let mapped = mapTemplateHref(fullUrl);
        if (mapped) {
            return `:href="${mapped}"`;
        }
        return match;
    });

    if (content !== newContent) {
        fs.writeFileSync(filePath, newContent, 'utf8');
        modifiedFiles++;
        console.log(`Updated template hrefs in ${filePath}`);
    }
});

console.log(`Modified ${modifiedFiles} files.`);
