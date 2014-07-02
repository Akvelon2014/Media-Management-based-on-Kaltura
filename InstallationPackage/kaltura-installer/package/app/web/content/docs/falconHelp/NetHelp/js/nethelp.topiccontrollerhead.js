var rootPath = document.getElementById('nethelpRootPath');
rootPath = rootPath && rootPath.getAttribute('href') || '';

var css = [
        'css/nethelp.index.css'
    ],
    js = [
        'js/jquery.js',
        'js/jquery.cookie.js',
        'js/jquery-ui.js',
        'js/jquery.scrollup.js',
        'js/nethelp.core.js',
        'js/nethelp.popup.js',
        'js/nethelp.index.js',
        'js/nethelp.shell.js',
        'js/nethelp.shell.drivers.js',
        'js/nethelp.shell.plugins.js',
        'js/nethelp.topiccontroller.js'
    ],
    i;

document.write('<link rel="stylesheet" href="' + rootPath + 'css/wijmo/aristo/jquery-wijmo.css" class="ui-theme">');
for (i in css) {
    document.write('<link rel="stylesheet" href="' + rootPath + css[i] + '">');
}
for (i in js) {
    document.write('<script type="text/javascript" src="' + rootPath + js[i] + '"></script>');
}
