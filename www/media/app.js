/**
 * Created by snowgirl on 4/14/17.
 */


/**
 * Very simple client app
 * @todo split into separate classes (views, cart, user, product..)
 * @todo add error handlers
 * @todo remove code duplicates if exists
 * @todo implement promises
 * @todo cache
 */
var restCrawlerApp = function (element, config) {
    this.iniArgs(element, config);
    var cachedState = this.getState();
    this.iniDOM(cachedState ? cachedState : this.constructor.STATE_LOAD);
};

restCrawlerApp.STATE_LOAD = 0;
restCrawlerApp.STATE_PICK = 1;

restCrawlerApp.prototype.iniDOM = function (state, callback) {
    this.setState(state);

    if (this.isState(this.constructor.STATE_LOAD)) {
        this.showViewLoad(callback);
    } else if (this.isState(this.constructor.STATE_PICK)) {
        this.showViewPick(callback);
    }
};

restCrawlerApp.prototype.getState = function () {
    return parseInt(this.getCache('state'));
};
restCrawlerApp.prototype.isState = function (state) {
    return state === this.getState();
};
restCrawlerApp.prototype.setState = function (state) {
    return this.setCache('state', state);
};

restCrawlerApp.prototype.iniArgs = function (element, config) {
    this.view = $('#' + element);

    this.config = $.extend({
        isCrawlOnClientSide: false
    }, config);

    this.storagePrefix = 'crawler-';

    if (this.config['isCacheItems']) {
        this.config['isCacheItems'] = 'items';
    }
};

restCrawlerApp.prototype.normalizeItems = function (items) {
    return items;
};
restCrawlerApp.prototype.setItems = function (items) {
    return this.setCache('items', this.normalizeItems(items));
};

/**
 * @todo... use some modern plugin for ajax  uploading
 *
 * @param $form
 * @param callback
 */
restCrawlerApp.prototype.onItemsLoaded = function ($form, callback) {
    this.request('post', 'items', new FormData($form[0]), function (code, items) {
        //@todo error processing

        if ([200, 201].indexOf(code) > -1) {
            this.setItems(items);
            $.proxy(callback, this)();
        }
    }, {
        cache: false,
        contentType: false,
        processData: false
    });
};

restCrawlerApp.prototype.makeUri = function (path) {
    return this.config['apiEndpoint'] + '/' + path;
};

restCrawlerApp.prototype.clearStorage = function () {
    $([sessionStorage, localStorage]).each($.proxy(function (index, storage) {
        var arr = [];

        for (var i = 0; i < storage.length; i++) {
            if (this.storagePrefix == storage.key(i).substring(0, this.storagePrefix.length)) {
                arr.push(storage.key(i));
            }
        }

        for (i = 0; i < arr.length; i++) {
            storage.removeItem(arr[i]);
        }
    }, this));
};

restCrawlerApp.prototype.request = function (method, uri, data, fn, addOptions, cacheKey) {
    addOptions = addOptions || {};

    if (cacheKey) {
        var cacheData = this.getCache(cacheKey);
        console.log('Cache Key: ', cacheKey);
        console.log('Cache Data: ', cacheData);
        if (cacheData) {
            $.proxy(fn, this)(cacheData.code, cacheData.response);
            return true;
        }
    }

    this.view.addClass('loading');

    var options = {url: this.makeUri(uri), dataType: 'json', type: method, data: data};
    options = $.extend(true, {}, options, addOptions);

    return $.ajax(options)
        .always($.proxy(function (response, code) {
            this.view.removeClass('loading');

            if (response) {
                code = response.hasOwnProperty('responseJSON') ? response['responseJSON']['code'] : response['code'];
                response = response.hasOwnProperty('responseJSON') ? response['responseJSON']['body'] : response['body'];
            } else if ('nocontent' == code) {
                code = 204;
            }

            if (cacheKey) {
                this.setCache(cacheKey, {code: code, response: response});
            }

            $.proxy(fn, this)(code, response);
        }, this));
};
restCrawlerApp.prototype.normalizeView = function (className) {
    this.view.removeAttr('class').empty();

    var mapCurrentToPreviousState = {};

    mapCurrentToPreviousState[this.constructor.STATE_PICK] = $.proxy(function () {
        this.clearStorage();
        this.iniDOM(this.constructor.STATE_LOAD);
    }, this);

    var currentState = this.getCache('state');

    if (mapCurrentToPreviousState.hasOwnProperty(currentState)) {
        var $btnBack = $('<button/>', {
            type: 'button',
            text: 'Back'
        });

        $btnBack.on('click', function () {
            mapCurrentToPreviousState[currentState]();
        });

        this.view.append($btnBack);

        var $btnClear = $('<button/>', {
            type: 'button',
            text: 'Clear'
        });

        $btnClear.on('click', $.proxy(function () {
            mapCurrentToPreviousState[this.constructor.STATE_PICK]();
        }, this));

        this.view.append($btnClear);
    }

    this.view.addClass(className);
};

restCrawlerApp.prototype.showViewLoad = function () {
    this.normalizeView('load');

    var $h2 = $('<h2/>', {text: 'Import'});

    this.view.append($h2);

    var $form = $('<form/>', {action: this.makeUri('items'), method: 'POST', enctype: 'multipart/form-data'});

    var $inputName = $('<input/>', {
        name: 'file',
        type: 'file',
        required: true
    });

    $form.append($('<label/>').append($('<span/>', {text: 'The File'})).append($inputName));

    var $btn = $('<button/>', {
        type: 'submit',
        text: 'OK'
    });

    $form.append($('<label/>').append($('<span/>')).append($btn));

    $form.on('submit', $.proxy(function (ev) {
        var $form = $(ev.target).closest('form');
//        var form = objectifyForm($(ev.target).serializeArray());

        this.onItemsLoaded($form, function () {
            this.iniDOM(this.constructor.STATE_PICK);
        });

        return false;
    }, this));

    this.view.append($form);
    $inputName.focus();
};

restCrawlerApp.prototype.makeOffersView = function (columns, offers) {
    var columnsLength = columns.length;

    if (!columnsLength) {
        return $('<h3/>', {text: 'No Columns'});
    }

    var offersLength = offers.length;

    if (!offersLength) {
        return $('<h3/>', {text: 'No Offers'});
    }

    var $offers = $('<table/>', {class: 'offers'});
    $offers.append($('<caption/>', {text: 'Offers'}));

    var $head = $('<tr/>');

    for (var i = 0; i < columnsLength; i++) {
        $head.append($('<th/>', {text: columns[i]}));
    }

    $offers.append($head);

    for (i = 0; i < offersLength; i++) {
        var $tr = $('<tr/>', {'data-id': offers[i]['id']});

        for (var j = 0; j < columnsLength; j++) {
            $tr.append($('<td/>', {text: offers[i][columns[j]]}));
        }

        $offers.append($tr);
    }

    return $offers;
};

restCrawlerApp.prototype.makeItemsPickView = function (items) {
    var itemsLength = items.length;

    if (!itemsLength) {
        return $('<h3/>', {text: 'No Items'});
    }

    var $items = $('<div/>', {class: 'items'});
    $items.append($('<h3/>', {text: 'Items To Pick Up'}));

    var $list = $('<div/>', {class: 'list'});

    for (var i = 0; i < itemsLength; i++) {
        $list.append($('<button/>', {
            class: 'item',
            type: 'button',
            text: items[i]
        }));
    }

    $list.on('click', '.item', function (ev) {
        var $item = $(ev.target);
        $item.toggleClass('active');

        if ($list.find('.item.active').length) {
            $btnCrawl.show();
        } else {
            $btnCrawl.hide();
        }
    });

    $items.append($list);

    var $btnCrawl = $('<button/>', {
        class: 'crawl',
        type: 'button',
        text: 'Crawl'
    });

    var $output = $('<div/>', {class: 'output'});

    $btnCrawl.on('click', $.proxy(function () {
        var items = $.map($list.find('.active'), function (item) {
            return item.innerHTML;
        });
        this.$offers.find('tr').removeClass('active');
        this.crawl(items, function (data) {
            var id = data['id'];
            $output.html('').append([
                'id = ' + (id ? id : 'NONE'),
                data['id'] ? ('Price = ' + ('<span class="price">' + data['price'] + '</span>')) : ''
            ].join('<br/>'));

            if (id) {
//                this.$offers.find('tr[data-id=' + id + ']').addClass('active');
                this.$offers.find('tr[data-id=' + id + ']').each(function (i, tr) {
                    var $tr = $(tr);
                    var content = $tr.text();

                    for (i = 0; i < items.length; i++) {
                        //@todo fix regex in case of substrings...
                        var regexp = new RegExp(items[i]);
                        if (regexp.test(content)) {
                            $tr.addClass('active');
                            return true;
                        }
                    }
                });
            }

            $("html, body").animate({scrollTop: $(document).height() - $(window).height()});
        });
    }, this));

    $items.append($btnCrawl.hide());
    $items.append($output);

    return $items;
};

restCrawlerApp.prototype.showViewPick = function (callback) {
    this.request('get', 'items', {}, function (code, data) {
        //@todo error processing
        this.normalizeView('items');

        this.view.append($('<h2/>', {html: 'What are you looking for?'}));
        this.$offers = this.makeOffersView(data['columns'], data['offers']);
        this.view.append(this.$offers);
        this.view.append(this.makeItemsPickView(data['items']));

        callback && $.proxy(callback, this)();
    }, null, this.config['isCacheItems']);
};
/**
 * @todo...
 *
 * @param items
 * @param callback
 */
restCrawlerApp.prototype.crawlOnClientSide = function (items, callback) {
};
restCrawlerApp.prototype.crawlOnServerSide = function (items, callback) {
    this.request('get', 'restaurant', {items: items}, function (code, data) {
        //@todo error processing

        if ([200, 201].indexOf(code) > -1) {
            callback(data);
        }
    });
};

restCrawlerApp.prototype.crawl = function (items, callback) {
    callback = $.proxy(callback, this);
    if (this.config.isCrawlOnClientSide) {
        this.crawlOnClientSide(items, callback);
    } else {
        this.crawlOnServerSide(items, callback);
    }
};

restCrawlerApp.prototype.setCache = function (k, v) {
    var json = JSON.stringify(v);

    k = this.storagePrefix + k;

    if (sessionStorage) {
        sessionStorage.setItem(k, json);
    }

    if (localStorage) {
        localStorage.setItem(k, json);
    }
};
restCrawlerApp.prototype.getCache = function (k) {
    k = this.storagePrefix + k;

    var v;

    if (!v) {
        if (localStorage && (v = localStorage.getItem(k))) {
            v = JSON.parse(v);
        }
    }

    if (!v) {
        if (sessionStorage && (v = sessionStorage.getItem(k))) {
            v = JSON.parse(v);
        }
    }

    if (v) {
        this[k] = v;
    }

    return v;
};