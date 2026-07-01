const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

function mapApiRoute(method, urlString) {
    let isLiteral = urlString.startsWith("'") || urlString.startsWith('"');
    let isTemplate = urlString.startsWith('`');
    let inner = urlString.substring(1, urlString.length - 1);
    
    if (isLiteral) {
        if (inner.startsWith('/')) {
            let parts = inner.substring(1).split('/');
            let resource = parts[0];
            
            if (parts.length === 1) {
                if (method === 'get') return `route('${resource}.index')`;
                if (method === 'post') return `route('${resource}.store')`;
            }
        }
    } else if (isTemplate) {
        let match = inner.match(/^\/([a-z_]+)\/\$\{([^}]+)\}(?:\/([a-z_]+))?$/);
        if (match) {
            let resource = match[1];
            let id = match[2];
            let action = match[3];
            
            if (!action) {
                if (method === 'get') return `route('${resource}.show', ${id})`;
                if (method === 'put' || method === 'patch' || method === 'post') return `route('${resource}.update', ${id})`;
                if (method === 'delete') return `route('${resource}.destroy', ${id})`;
            } else {
                if (resource === 'pagos') {
                    if (action === 'pagofacil') return `route('pagos.pagofacil', ${id})`;
                    if (action === 'status') return `route('pagos.status', ${id})`;
                    if (action === 'efectivo') return `route('pagos.efectivo', ${id})`;
                }
                if (resource === 'cotizaciones') {
                    if (action === 'estado') return `route('cotizaciones.estado.update', ${id})`;
                    if (action === 'enviar_y_guardar') return `route('cotizaciones.enviar_y_guardar', ${id})`;
                }
            }
        }
    }
    
    return null;
}

let modifiedFiles = 0;

walkDir('resources/js', function(filePath) {
    if (!filePath.endsWith('.vue')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let newContent = content;

    let regex = /(form|router|axios|enviarForm|estadoForm|efectivoForm)\.(get|post|put|patch|delete)\(\s*('(\/[^']+)'|"(\/[^"]+)"|`(\/[^`]+)`)/g;
    
    newContent = newContent.replace(regex, (match, caller, method, fullUrl) => {
        let mapped = mapApiRoute(method, fullUrl);
        if (mapped) {
            return `${caller}.${method}(${mapped}`;
        }
        return match;
    });

    if (content !== newContent) {
        fs.writeFileSync(filePath, newContent, 'utf8');
        modifiedFiles++;
        console.log(`Updated API routes in ${filePath}`);
    }
});

console.log(`Modified ${modifiedFiles} files.`);
