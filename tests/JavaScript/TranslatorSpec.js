define(['../../pub/js/lib/translator'], function (translate) {
    describe('Translator', function () {
        it('returns same string if no translations are provided', function () {
            expect(translate('foo')).toBe('foo');
        });

        it('returns same string if no translation for given string is defined', function () {
            window.translations = {};
            expect(translate('foo')).toBe('foo');
            delete window.translations;
        });

        it('returns translation', function () {
            window.translations = {"foo": 'bar'};
            expect(translate('foo')).toBe('bar');
            delete window.translations;
        });

        it('returns translation with placeholder if no replacement is provided', function () {
            window.translations = {"foo %s": 'bar %s'};
            expect(translate('foo %s')).toBe('bar %s');
            delete window.translations;
        });

        it('returns translation with placeholders replaced with given agruments', function () {
            window.translations = {"foo %s %s": 'bar %s %s'};
            expect(translate('foo %s %s', 'baz', 'qux')).toBe('bar baz qux');
            delete window.translations;
        });
    });
});
