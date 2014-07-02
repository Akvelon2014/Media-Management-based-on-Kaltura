(function ($, core, undefined) {

//#region Helpers
var searchAnd,
    searchOr,
    searchNot,
    searchAndInSpaces,
    searchOrInSpaces,
    searchNotInSpaces = searchNot + " ";
var d2hQuotePrefix = "_d2hQuote";
var quotes = new Array();
var aliasesHT = new Array();

function isFarEasternLanguage(s) {
    var codes = [[0x2E80, 0x9FFF], [0xA000, 0xA63F], [0xA6A0, 0xA71F], [0xA800, 0xA82F], [0xA840, 0xD7FF], [0xF900, 0xFAFF], [0xFE30, 0xFE4F]];
    for (var i = 0; i < s.length; i++) {
        var code = s.charCodeAt(i);
        if (code < codes[0][0])
            continue;
        for (var j = 0; j < codes.length; j++)
            if (code >= codes[j][0] && code <= codes[j][1])
                return true;
    }
    return false;
}

function addSpace(str, words, startIndex, checkForEastern) {
    for (var i = startIndex; i < words.length; i++) {
        var pos = str.indexOf(" ", 0);
        if (pos != -1)
            return addSpace(str.substring(0, pos), words, i, checkForEastern) + " " + addSpace(str.substring(pos + 1), words, i, checkForEastern);
        else if (pos == -1 && (!checkForEastern || isFarEasternLanguage(str))) {
            pos = str.indexOf(words[i], 0);
            if (pos != -1) {
                var left = addSpace(str.substring(0, pos), words, i, checkForEastern);
                var right = addSpace(str.substring(pos + words[i].length), words, i, checkForEastern);
                if (left && left != "\"")
                    left += " ";
                if (right && right != "\"")
                    right = " " + right;
                var newStr = words[i];
                if (left)
                    newStr = left + newStr;
                if (right)
                    newStr += right;
                return newStr;
            }
        }
    }
    return str;
}

function getWildcardRegexp(wildcard, allowSubwords) {
    wildcard = wildcard
        .replace(/[-[\]{}()+.,\\^$|]/g, '\\$&')
        .replace(/\*+/g, '\\w*')
        .replace(/\?/g, '\\w')
        .replace(/\s+/g, '\\s+');
    if (wildcard === '\\w*') {
        wildcard = '\\w+';
    }
    if (!isFarEasternLanguage(wildcard) && !allowSubwords) {
        var l = wildcard.length,
            startIsWordChar = /^(\w|\.|\\w)/,
            endIsWordChar = /(\w|\.|\*|\\w\+)$/;
        wildcard = (startIsWordChar.test(wildcard) ? '\\b' : '') + 
            wildcard +
            (endIsWordChar.test(wildcard) ? '\\b' : '');
    }
    return wildcard;
}

function Clause(str, quotes) {
    if (!this || !this._prepare) {
        return new Clause(str, quotes);
    }
    if (!quotes) {
        quotes = {};
        str = this._prepare(str, quotes);
    }
    str = str || '';
    var exactPhrase = (str.indexOf(d2hQuotePrefix) == 0 && quotes[str] != null) || (str.toLowerCase().indexOf((searchNotInSpaces + d2hQuotePrefix).toLowerCase()) == 0 && quotes[str.substring(searchNotInSpaces.length, str.length)] != null);
    var parts;
    var foundNot = false;
    if (!exactPhrase) {
        parts = str.split(new RegExp(searchOrInSpaces.replace(/[-[\]{}()*+?.,\\^$|]/g, '\\$&'), "gi"));
        if (parts.length == 1) {
            parts = str.split(new RegExp(searchAndInSpaces.replace(/[-[\]{}()*+?.,\\^$|]/g, '\\$&'), "gi"));
            this.type = 2;
        }
        else {
            this.type = 1;
        }
        if (parts.length == 1) {
            foundNot = str.toUpperCase().indexOf(searchNotInSpaces) === 0;
            if (foundNot) {
                str = str.substring(searchNotInSpaces.length, str.length);
            }
            parts = str.split(" ");
            this.type = 2;
        }
        parts = removeRepeatingTerms(parts);
    }
    else {
        foundNot = str.toUpperCase().indexOf(searchNotInSpaces) === 0;
        if (foundNot) {
            str = str.substring(searchNotInSpaces.length, str.length);
        }
        this.type = 0;
        parts = quotes[str].split(" ");
        str = quotes[str];
    }
    if (parts.length > 1 || exactPhrase) {
        this.children = new Array(parts.length);
        for (var i = 0; i < parts.length; i++) {
            this.children[i] = new Clause(parts[i], quotes);
            if (exactPhrase) {
                this.children[i].type = 0;
            }
        }
    }
    if (foundNot) {
        if (parts.length == 1 || exactPhrase) {
            this.not = true;
        }
        else {
            this.children[0].not = true;
        }
    }
    this.value = str.toLowerCase();
}

Clause.prototype = {
    value: "",
    children: null,
    type: 0, //0 - exact phrase, 1 - OR, 2 - AND
    not: false,
    docs: null,
    _prepare: function(str, quotes) {
        if (!str) {
            return;
        }
        str = addSpace(str, getWords(), 0, true);
        var i = 0;
        str = str.replace(/"([^"]+)"/g, function(m, phrase) {
            var key = d2hQuotePrefix + (i++);
            quotes[key] = phrase;
            return key;
        }).replace(/[\.,";(){}[\]]/g, " ");
        for (var i = 0; i < g_sStopWords.length; i++) {
            var word = g_sStopWords[i].toUpperCase();
            if (word === searchOr || word === searchAnd || word === searchNot)
                continue;
            str = str.replace(RegExp("(^|\\s)" + g_sStopWords[i] + "($|\\s)", 'ig'), " ");
        }
        return addAND(str
            .replace(/\s+/g, " ")
            .replace(/^\s+/g, "")
            .replace(/\s+$/g, ""));
    },
    execute: function() {
        if (this.children == null) {
            if (this.type != 0) {
                var aliases = getAliases(this.value);
                for (var i = 0; i < aliases.length; i++)
                    this.docs = mergeDocs(this.docs, searchInIndex(aliases[i], true));
            }
            else
                this.docs = searchInIndex(this.value, this.type != 0);
            if (!this.docs && !isFarEasternLanguage(this.value)) {
                var newString = addSpace(this.value, getWords(), 0, false);
                if (newString != this.value) {
                    var words = newString.split(" ");
                    for (var i = 0; i < words.length; i++) {
                        if (this.type == 0 && getWordIndex(g_sStopWords, words[i]) != -1)
                            continue;
                        var documents = searchInIndex(words[i], true);
                        if (!documents) {
                            this.docs = null;
                            break;
                        }
                        this.docs = !this.docs ? documents : intersect(this.docs, documents, true);
                    }
                }
            }

        }
        else {
            for (var i = 0; i < this.children.length; i++) {
                if (this.type == 0 && getWordIndex(g_sStopWords, this.children[i].value) != -1)
                    continue;
                if (this.children[i].execute()) {
                    if (this.type == 0 || this.type == 2)
                        this.docs = !this.docs ? this.children[i].docs : intersect(this.docs, this.children[i].docs, this.type == 0);
                    else
                        this.docs = !this.docs ? this.children[i].docs : mergeDocs(this.docs, this.children[i].docs);
                }
                else if (this.type != 1 || i == 0)
                    this.docs = null;
                if (this.docs == null && this.type != 1)
                    break;
            }
        }
        if (this.not && this.docs)
            this.docs = invert(this.docs);
        return this.docs != null;
    },
    getQueryString: function(corrected) {
        if (this.children == null) {
            var word;
            if (corrected && this.type != 0 && !isWildcard(this.value) && (!this.docs || this.docs.length < 2) && !aliasesHT[this.value])
                // looks misspelled
                word = getSimilarWord(this.value);
            else
                word = this.value;
            return this.not && this.type != 0 ? searchNotInSpaces + word : word;
        }
        else {
            var query = "";
            var typeName = this.type == 1 ? searchOrInSpaces : " ";
            for (var i = 0; i < this.children.length; i++) {
                var subquery = this.children[i].getQueryString(corrected);
                if (i == 0)
                    query = subquery;
                else if (this.children[i].not && this.type == 2)
                    query += searchAndInSpaces + subquery;
                else
                    query += typeName + subquery;
            }
            if (this.type == 0) {
                query = "\"" + query + "\"";
                if (this.not)
                    query = searchNotInSpaces + query;
            }
            return query;
        }
    },
    getWords: function(usePhrase) {
        if (!this.children || usePhrase && this.type === 0) {
            return this.not ? [] : [ this.value ];
        }
        return $.map(this.children, function(i) {
            return i.getWords();
        });
    }
}

function defineOperators(and, or, not) {
    searchAnd = and.toUpperCase();
    searchOr = or.toUpperCase();
    searchNot = not.toUpperCase();
    searchAndInSpaces = " " + searchAnd + " ";
    searchOrInSpaces = " " + searchOr + " ";
    searchNotInSpaces = searchNot + " ";
}

defineOperators('AND', 'OR', 'NOT');

function getWords() {
    var wordsSorted = new Array();
    wordsSorted.length = g_sWords.length;
    for (var i = 0; i < g_sWords.length; i++)
        wordsSorted[i] = g_sWords[i];
    wordsSorted.sort(sortByWordsLength);
    return wordsSorted;
}

function sortByWordsLength(x, y) {
    var delta = x.length - y.length;
    if (delta < 0)
        return 1;
    if (delta > 0)
        return -1;
    return 0;
}

function getAliases(word) {
    initAliases();
    var indexes = aliasesHT[word];
    var words;
    if (indexes && (typeof indexes != "function")) {
        words = g_sAliases[indexes[0]];
        for (var i = 1; i < indexes.length; i++)
            words = mergeSimple(words, g_sAliases[indexes[i]]);
    }
    else {
        words = new Array(1);
        words[0] = word;
    }
    return words;
}

function getDistance(x, y, maxDelta) {
    if (x.charAt(0) != y.charAt(0) || Math.abs(x.length - y.length) > 2)
        return maxDelta + 1;
    var N = x.length + 1, M = y.length + 1, min;
    var a = new Array(N);
    for (var i = 0; i < N; i++) {
        a[i] = new Array(M);
        a[i][0] = i;
    }
    for (var i = 0; i < M; i++)
        a[0][i] = i;
    for (var i = 1; i < N; i++) {
        min = N + M;
        for (var j = 1; j < M; j++) {
            a[i][j] = Math.min(a[i - 1][j - 1] + (x.charAt(i - 1) == y.charAt(j - 1) ? 0 : 1), Math.min(a[i - 1][j], a[i][j - 1]) + 1);
            if (a[i][j] < min)
                min = a[i][j];
        }
        if (min > maxDelta)
            return maxDelta + 1;
    }
    return a[N - 1][M - 1];
}

function getSimilarWord(w) {
    if (isFarEasternLanguage(w))
        return w;
    var maxDelta = 3 * Math.round(0.4 + w.length / 10);
    var bestWordIndex = -1, bestLength = -1;
    for (var i = 0; i < g_sWords.length; i++) {
        var word = g_sWords[i], topicsCount = getWordTopicsItemLength(i);
        if (topicsCount == 1 && !aliasesHT[word])
            continue;
        var d = getDistance(word, w, maxDelta);
        if (d > maxDelta || d == 0)
            continue;
        if (d < maxDelta || bestWordIndex == -1) {
            maxDelta = d;
            bestWordIndex = i;
            bestLength = topicsCount;
        }
        else if (topicsCount > bestLength) {
            bestWordIndex = i;
            bestLength = topicsCount;
        }
    }
    return bestWordIndex != -1 ? g_sWords[bestWordIndex] : w;
}

var _aliasesInited = false;
function initAliases() {
    if (_aliasesInited) {
        return;
    }
    for (var i = 0; i < g_sAliases.length; i++) {
        for (var j = 0; j < g_sAliases[i].length; j++) {
            var word = g_sAliases[i][j];
            if (aliasesHT[word] == null)
                aliasesHT[word] = new Array(1);
            aliasesHT[word][aliasesHT[word].length - 1] = i;
        }
    }
    _aliasesInited = true;
}

function addAND(str) {
    for (var i = str.length - searchNotInSpaces.length - 1; i >= 0; i--)
        if (str.substring(i, i + searchNotInSpaces.length + 1).toUpperCase() == " " + searchNotInSpaces) {
            var startIndex = i - searchAndInSpaces.length + 1;
            var found = startIndex >= 0 ? str.substring(startIndex, i + 1).toUpperCase() == searchAndInSpaces : false;
            if (!found) {
                startIndex = i - searchOrInSpaces.length + 1;
                found = startIndex >= 0 ? str.substring(startIndex, i + 1).toUpperCase() == searchOrInSpaces : false;
            }
            if (!found)
                str = str.substring(0, i) + " " + searchAnd + str.substring(i, str.length);
        }
    return str;
}

function getDocID(arr) {
    return arr[0];
}

function invert(docs) {
    var docsLength = docs ? docs.length : 0;
    var cnt = g_sTopics.length - docsLength;
    var newDocs = new Array(cnt);
    var j = 0, l = 0;
    var id = j < docsLength ? getDocID(docs[j++]) : g_sTopics.length;
    for (var i = 0; i < g_sTopics.length; i++) {
        if (i < id) {
            newDocs[l] = new Array(1);
            newDocs[l][0] = i;
            l++;
        }
        else if (i == id)
            id = j < docsLength ? getDocID(docs[j++]) : g_sTopics.length;
    }
    return newDocs;
}

function intersect(docs1, docs2, exactPhrase) {
    if (!docs1  || !docs2)
        return null; 
    var docs = new Array(docs1.length);
    var i = 0, j = 0, k = 0;
    while (i < docs1.length && j < docs2.length) {
        var id1 = getDocID(docs1[i]), id2 = getDocID(docs2[j]);
        if (id1 == id2) {
            var p1 = 1, p2 = 1, p = 1;
            var positions = new Array();
            positions[0] = id1;
            if (exactPhrase) {
                while (p1 < docs1[i].length && p2 < docs2[j].length) {
                    if (docs1[i][p1] == docs2[j][p2] - 1) {
                        positions[p++] = docs2[j][p2];
                        p1++;
                        p2++;
                    }
                    while (p1 < docs1[i].length && docs1[i][p1] < docs2[j][p2] - 1)
                        p1++;
                    while (p2 < docs2[j].length && docs2[j][p2] <= docs1[i][p1])
                        p2++;
                }
            }
            if (!exactPhrase || positions.length > 1) {
                docs[k] = positions;
                k++;
            }
            i++;
            j++;
        }
        else if (id1 < id2) {
            while (i < docs1.length && getDocID(docs1[i]) < id2)
                i++;
        }
        else {
            while (j < docs2.length && getDocID(docs2[j]) < id1)
                j++;
        }
    }
    if (docs.length > k)
        docs.length = k;
    return docs;
}

function mergeSimple(x, y) {
    if (x == null)
        return y;
    if (y == null)
        return x;
    var res = new Array(x.length + y.length);
    var i = 0, j = 0, k = 0;
    while (i < x.length && j < y.length) {
        if (x[i] == y[j]) {
            res[k++] = x[i++];
            j++;
        }
        else
            res[k++] = x[i] < y[j] ? x[i++] : y[j++];
    }
    while (i < x.length)
        res[k++] = x[i++];
    while (j < y.length)
        res[k++] = y[j++];
    if (res.length > k)
        res.length = k;
    return res;
}

function mergeDocs(x, y) {
    if (x == null)
        return y;
    if (y == null)
        return x;
    var res = new Array(x.length + y.length);
    var i = 0, j = 0, k = 0;
    while (i < x.length && j < y.length) {
        var id1 = getDocID(x[i]), id2 = getDocID(y[j]);
        if (id1 == id2) {
            res[k] = new Array(2);
            res[k++] = mergeSimple(x[i++], y[j++]);
        }
        else
            res[k++] = id1 < id2 ? x[i++] : y[j++];
    }
    while (i < x.length)
        res[k++] = x[i++];
    while (j < y.length)
        res[k++] = y[j++];
    if (res.length > k)
        res.length = k;
    return res;
}

function getWordIndex(words, word) {
    var l = 0, r = words.length - 1;
    while (r > l) {
        var m = Math.round((l + r) / 2);
        if (words[m] < word)
            l = m + 1;
        else if (words[m] > word)
            r = m - 1;
        else
            return m;
    }
    return l == r && words[l] == word ? l : -1;
}

function getWordTopicsItemLength(i) {
    var j = 0, k = 0;
    while (j < g_sWordTopics[i].length) {
        k++;
        j += g_sWordTopics[i][j + 1] + 2;
    }
    return k;
}

function getWordTopicsItem(i) {
    var arr = new Array(getWordTopicsItemLength(i));
    var j = 0, k = 0;
    while (j < g_sWordTopics[i].length) {
        arr[k] = new Array(g_sWordTopics[i][j + 1] + 1);
        arr[k][0] = g_sWordTopics[i][j];
        for (var l = 0; l < g_sWordTopics[i][j + 1]; l++)
            arr[k][l + 1] = g_sWordTopics[i][j + 2 + l];
        k++;
        j += g_sWordTopics[i][j + 1] + 2;
    }
    return arr;
}

function searchInIndex(term, allowWildcards) {
    var wildcard = allowWildcards && isWildcard(term);
    if (wildcard) {
        var re = new RegExp(getWildcardRegexp(term), "gi");
        var indx;
        var res = null;
        for (var i = 0; i < g_sWords.length; i++) {
            indx = g_sWords[i].search(re);
            if (indx > -1) {
                if (res)
                    res = mergeDocs(res, getWordTopicsItem(i));
                else
                    res = getWordTopicsItem(i);
            }
        }
        return res;
    }
    else {
        var index = getWordIndex(g_sWords, term);
        return index != -1 ? getWordTopicsItem(index) : null;
    }
}

function getWordsFromIndex(termWithWildcards) {
    var words = new Array();
    var re = new RegExp(getWildcardRegexp(termWithWildcards), "gi");
    for (var i = 0; i < g_sWords.length; i++)
        if (g_sWords[i].search(re) > -1) {
            words.length = words.length + 1;
            words[words.length - 1] = g_sWords[i];
        }
    return words;
}

function isWildcard(term) {
    return term.indexOf("?") > -1 || term.indexOf("*") > -1;
}

function removeRepeatingTerms(terms) {
    var htbl = new Array();
    var res = new Array();
    for (var i = 0; i < terms.length; i++)
        if (!htbl[terms[i]]) {
            res[res.length] = terms[i];
            htbl[terms[i]] = true;
        }
    return res;
}

function calcHistogram(arr) {
    var tbl = new Array();
    var id;
    for (var i = 0; i < arr.length; i++) {
        id = "x" + arr[i][0];
        if (tbl[id]) {
            tbl[id] = new Array(tbl[id].length + arr[i].length);
            arr[i] = null;
        }
        else
            tbl[id] = arr[i];
    }
    arr.sort(sortByCounterNumber);
    return arr;
}

function sortByCounterNumber(x, y) {
    if (x == null)
        return 1;
    if (y == null)
        return -1;
    var delta = x.length - y.length;
    if (delta == 0) {
        var xTopic = g_sTopics[x[0]][1];
        var yTopic = g_sTopics[y[0]][1];
        if (xTopic > yTopic)
            return 1;
        else if (xTopic < yTopic)
            return -1;
        return 0;
    }
    if (delta < 0)
        return 1;
    return -1;
}

function highlight(element, word, className, allowSubwords) {
    element = $(element);
    if (element.hasClass(className)) {
        return;
    }
    if (typeof word === 'string') {
        word = new RegExp(getWildcardRegexp(word, allowSubwords), 'i');
    }
    element.contents().each(function() {
        var n = this, h, m, l;
        if (n.nodeType === 3 || n.nodeType === 4) {
            // The text from text nodes and CDATA nodes
            while (m = word.exec(n.nodeValue || '')) {
                h = m.index ? n.splitText(m.index) : n;
                m = m[0];
                l = m.length;
                n = h.nodeValue.length > l ? h.splitText(l) : 0;
                $(h).wrap('<span class="' + className + '" />');
            }
        }
        else if (this.nodeType !== 8) {
            highlight(this.nodeType === 9 ? $('body', this) : this, 
                word, className);
        }
    });
    element.trigger('highlight');
}
function unhighlight(element, className) {
    $element = $(element);
    $element.find('.' + className).map(function() {
        return $(this.firstChild).unwrap()[0];
    }).parent().each(function() {;
        this.normalize();
    });
    $element.find('iframe').each(function() {
        unhighlight(this.contentWindow.document, className);
    });
    $element.trigger('unhighlight');
}
//#endregion

//#region Constants
var p = 'c1-search',
    c_item = p + '-item',
    d_item = 'searchItem',
    p_itemOf = '__c1ItemOfSearch';
//#endregion

var Search = core.widget('search', {
    options: {
        dataSource: 'searchindex.js',
        filterElement: undefined,
        selectedClass: 'c1-search-selected',
        highlight: {
            disabled: false,
            element: undefined,
            className: 'search-highlight'
        },
        operators: {
            and: 'AND',
            or: 'OR',
            not: 'NOT'
        },
        itemTemplate: '<li class="#{itemClass}"><a class="c1-search-text" id="#{id}" href="#{url}">#{text}</a></li>',
        moreTemplate: '<li class="#{itemClass}"><a href="javascript:void(0)" class="c1-search-more-text">#{text}</a></li>',

        scrollElement: undefined,
        disableScrollEvent: false,
        pageSize: 300,
        moreText: 'More...'
    },
    _create: function () {
        var self = this,
            _ready = self._Ready(),
            element = self.element,
            options = self.options,
            operators = options.operators,
            itemTemplate = options.itemTemplate;

        defineOperators(operators.and, operators.or, operators.not);

        // templates in options
        core.each([ 'itemTemplate', 'moreTemplate' ], function(o) {
            var t = options[o]
            if (core.isString(t)) {
                options[o] = core.template(t);
            }
        });

        // init element
        element.addClass(p + ' c1-widget');
        self.items = [];
        self._bindEvents();
        self.disable();
        var timer = setTimeout(function() { self.trigger('loading'); }, 500);
        core.includeScript(options.dataSource, function() {
            clearTimeout(timer);
            element.empty();
            self.enable();
            _ready.fire();
        }).fail(function(request, status, error) {
            _ready.cancel({ status: status, error: error, request: request });
        });
    },
    _bindEvents: function() {
        var self = this,
            element = self.element,
            options = self.options,
            operators = options.operators,
            filterElement = options.filterElement,
            scrollElement = self.scrollElement = options.scrollElement ? $(options.scrollElement) : element;
        element.delegate('.c1-search-text', 'click', function(e) {
            var target = $(this),
                li = self.getItem(target);
            e.preventDefault();
            self.deselect();
            target.addClass(options.selectedClass);
            self.trigger('select', null, { target: target, li: li, url: target.attr('href') });
        }).delegate('.correcting', 'click', function (e) {
            e.preventDefault();
            self.search($(this).text());
        }).delegate('.c1-search-more-text', 'click', function(e) {
            e.preventDefault();
            self.nextPage();
        });
        scrollElement.scroll(function(e) {
            if (!options.disableScrollEvent && scrollElement.scrollTop() > scrollElement[0].scrollHeight - scrollElement.innerHeight() - 10) {
                self.nextPage();
            }
        });
        //#region filterElement init
        if (!filterElement) {
            filterElement = element.data(Search.filterDataKey);
        }
        if (filterElement) {
            self.filterElement = filterElement = $(filterElement).eq(0)
                .keydown(function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        self.search(filterElement.val());
                    }
                });
        }
        //#endregion
    },
    _itemsHtml: function(items, start, end) {
        if (start == undefined) {
            start = 0;
        }
        end = Math.min(items.length, end || Infinity);
        var self = this,
            tmpl = self.options.itemTemplate,
            sb = core.StringBuilder(),
            i;
        for (i = start; i < end; ++i) {
            sb.addLine(tmpl(core.extend(true, { itemClass: c_item }, items[i])));
        }
        return sb.toString();
    },
    nextPage: function(element) {
        var self = this,
            options = self.options,
            size = self.htmlSize,
            items = self.items,
            l = items.length,
            size2 = l > size && size + options.pageSize;
        if (element) {
            // external element - show all
            size = 0;
            size2 = l;
        }
        else {
            element = self.element;
            if (size2) {
                self.htmlSize = Math.min(size2, l);
            }
        }
        if (size2) {
            var more = element.find(' > .c1-index-more').remove();
            element.append(self._itemsHtml(items, size,
                size2));
            if (size2 < l) {
                element.append(more.length ? more : options.moreTemplate({
                    itemClass: c_item + ' service c1-index-more',
                    text: options.moreText
                }));
            }
            self.trigger('nextpage', undefined, { first: !size, last: size2 >= l });
        }
    },
    getItem: function(li, returnNull) {
        if (!li) {
            return returnNull ? null : $();
        }
        var self = this;
        if (li[p_itemOf] === self) {
            return li;
        }
        li = $(li).eq(0).closest('li', self.element);
        li[p_itemOf] = self;
        return returnNull && !li.length ? null : li;
    },
    getData: function(li) {
        return this.getItem(li).data(d_item);
    },
    disable: function(event) {
        var self = this;
        self.options.disabled = true;
        self.element.empty();
        self.trigger('disabled', event, { setter: true });
    },
    enable: function() {
        var self = this;
        if (self.options.disabled) {
            self.options.disabled = false;
            self.element.find('.service').remove();
        }
    },
    search: function (text, element, callback) {
        var self = this,
            options = self.options,
            special;
        if (options.disabled) {
            return;
        }
        if ($.isFunction(element)) {
            callback = element;
            element = undefined;
        }
        element = element && $(element) || self.element;
        special = element !== self.element;
        self.highlight(false);
        if (self.scrollElement) {
            self.scrollElement.scrollTop(0);
        }
        element.scrollTop(0).empty();
        self.htmlSize = 0;
        text = text || self.query();
        //#region jExecQuery
        initAliases();
        var root = new Clause(text);
        root.execute();
        var original = root.getQueryString(false),
            correcting = root.getQueryString(true);
        if (original == correcting) {
            correcting = "";
        }
        self.query(original);
        self.highlight(root);
        if (root.docs) {
            root.docs = calcHistogram(root.docs);
        }
        //#endregion
        var i = 0;
        var items = self.items = core.map(root.docs || [], function(item) {
            item = item && g_sTopics[item[0]];
            if (item) {
                return {
                    url: item[0],
                    text: item[1],
                    id: 'searchResult' + (i++)
                };
            }
        });
        var count = items.length;
        var p = { element: element, correcting: correcting, count: count };
        if (count) {
            self.trigger('found', undefined, p);
            self.nextPage(special && element || undefined);
        }
        else {
            self.trigger('notfound', undefined, p);
        }
        $.isFunction(callback) && callback(self, element);
    },
    query: function (text) {
        var filter = this.filterElement;
        if (text == undefined) {
            return filter && filter.val() || '';
        }
        else if (filter) {
            filter.val(text);
        }
    },
    deselect: function() {
        var c_selected = this.options.selectedClass;
        this.element.find('.' + c_selected).removeClass(c_selected);
    },
    highlight: function(params) {
        /*
            params: {
                action: boolean,
                element: jQuery,
                words: string|Clause,
                aliases: boolean
            }
            or params: false - unhighlight
            or params: string|Clause
        */
        var self = this,
            action;
        if (typeof params === 'boolean') {
            params = { action: params };
        }
        else if (typeof params === 'string' || params instanceof Clause || $.isArray(params)) {
            params = { words: params };
        }
        params = $.extend({}, self.options.highlight, params);
        var element = params.element || self.element,
            className = params.className || 'highlight';
        if (params.action || params.action == undefined && !params.disabled) {
            var words = params.words || self.query();
            if (typeof words === 'string') {
                words = new Clause(words);
            }
            if (words instanceof Clause) {
                words = words.getWords(true);
            }
            if ($.isArray(words)) {
                if (params.aliases !== false) {
                    words = $.map(words, function(w) {
                        return getAliases(w);
                    });
                }
                var p;
                words = $.map(words.sort(), function(w) {
                    return w === p || !w ? undefined : (p = w);
                });
                for (var w in words) {
                    highlight(element, words[w], className, false);
                }
                if (!$('.' + className, element).lenght) {
                    for (var w in words) {
                        highlight(element, words[w], className, true);
                    }
                }
            }
        }
        else if (params.action != undefined) {
            unhighlight(params.element, params.className);
        }
    }
}); // widget
Search.filterDataKey = 'filter';

})(jQuery, nethelp);