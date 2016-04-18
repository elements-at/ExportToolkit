var gulp = require('gulp');

require('project-semver')(gulp, 'composer.json', {
    files: [
        'package.json',
        {
            file: 'plugin.xml',
            path: '//zend-config//plugin//pluginVersion'
        }
    ]
});
