#Installation
###1.Install
```composer require twin-elements/menu-bundle```

###2.Add to routes.yaml
```
te_menu:
    resource: "@TwinElementsMenuBundle/Controller/"
    prefix: /admin
    type: annotation
    requirements:
        _locale: '%app_locales%'
    defaults:
        _locale: '%locale%'
        _admin_locale: '%admin_locale%'
    options: { i18n: false }
```
###3.In bundles.php add
```
TwinElements\MenuBundle\TwinElementsMenuBundle::class => ['all' => true],
```

How it use
```
{% set menuName = 'menu_code' %}
{% set mainMenu = knp_menu_get('front_main_menu',[],{'category': menuName}) %}
{{ knp_menu_render(mainMenu, {'currentAsLink': true,'currentClass': 'active', 'ancestorClass': 'active', 'depth': 2, 'branch_class': 'has-sub'}) }}
```
