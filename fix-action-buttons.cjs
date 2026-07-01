const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(path.join(dir, f));
    });
}

const editClasses = 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3';
const showClasses = 'text-stone-600 hover:text-stone-800 dark:text-stone-400 dark:hover:text-stone-300 mr-3';
const deleteClasses = 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300';
const iconClasses = 'h-4 w-4 inline';

let modifiedFiles = 0;

walkDir('resources/js', function(filePath) {
    if (!filePath.endsWith('.vue')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let newContent = content;

    const tdPattern = /<td class="p-4 text-right"(.*?)>([\s\S]*?)<\/td>/g;
    
    newContent = newContent.replace(tdPattern, (match, tdAttrs, innerHtml) => {
        let modifiedInner = innerHtml;

        // Remove the div wrapper if it exists
        modifiedInner = modifiedInner.replace(/<div class="flex justify-end gap-2">\s*/g, '');
        modifiedInner = modifiedInner.replace(/\s*<\/div>\s*$/g, '\n                                ');
        
        const tagRegex = /<(Link|button)([^>]*)class="[^"]+"([^>]*)>\s*<(Edit|Trash2|Eye) class="[^"]+"\s*\/>\s*<\/\1>/g;
        
        modifiedInner = modifiedInner.replace(tagRegex, (match2, tag, beforeClass, afterClass, iconName) => {
            let newClass = '';
            if (iconName === 'Edit') newClass = editClasses;
            else if (iconName === 'Trash2') newClass = deleteClasses;
            else if (iconName === 'Eye') newClass = showClasses;
            
            return `<${tag}${beforeClass}class="${newClass}"${afterClass}>\n                                        <${iconName} class="${iconClasses}" />\n                                    </${tag}>`;
        });

        return `<td class="p-4 text-right whitespace-nowrap"${tdAttrs}>${modifiedInner}</td>`;
    });

    if (content !== newContent) {
        fs.writeFileSync(filePath, newContent, 'utf8');
        modifiedFiles++;
        console.log(`Updated buttons in ${filePath}`);
    }
});

console.log(`Modified ${modifiedFiles} files.`);
