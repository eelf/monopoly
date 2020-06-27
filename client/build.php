<?php
/**
 * @team QA <qa@corp.badoo.com>
 * @maintainer Evgeniy Makhrov <e.makhrov@corp.badoo.com>
 *
 * requirements: php node yarn
 *
 * steps:
 * 1. php build.php development
 * this will install dependencies into global directory $HOME/nodem, links into $HOME/.config/yarn
 * creates .npmrc webpack.config.js node_modules/
 *
 * 2. node_modules/webpack/bin/webpack.js -w
 * this will build application into ../tests.js and ../tests.js.map
 * and keep watching changes in files effectively rebuilding app
 *
 * 3. make changes in application and debug if needed
 *
 * 4. php build.php production
 *
 * 5. node_modules/webpack/bin/webpack.js
 *
 * 6. php build.php clean && rm -r node_modules
 *
 * 7. commit app sources and ../tests.js
 *
 * 8. (optionally) delete $HOME/nodem $HOME/.config/yarn
 *
 */

function run($cmd, $args = [])
{
    $args = array_map('escapeshellarg', $args);
    $cmd = implode(' ', array_merge([$cmd], $args));
    $timer = microtime(true);
    echo "exec $cmd ...\n";
    $ret = proc_close(proc_open($cmd, [], $pp));
    echo "exec = $ret in " . number_format((microtime(true) - $timer) * 1e6) . " us\n";
}

if (!in_array($mode = $argv[1] ?? null, ['production', 'development', 'clean'])) {
    echo "usage: build <production|development|clean>\n";
    exit(0);
}

chdir(__DIR__);

if ($mode == 'clean') {
    foreach (['.npmrc', 'webpack.config.js', 'yarn.lock', 'package.json', 'node_modules'] as $file) {
        if (is_dir($file)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                $file, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                if (is_dir($path) && !is_link($path)) rmdir($path);
                else unlink($path);
            }
            rmdir($file);
        } else if (file_exists($file)) {
            unlink($file);
        }
    }
    exit(0);
}

$yarn = array_reduce(
    ['/usr/bin/yarn'],
    function ($carry, $item) {
        return file_exists($item) ? $item : $carry;
    },
    'yarn'
);
$global_nm_prefix = "$_SERVER[HOME]/nodem";
$global_nm_dir = "$global_nm_prefix/lib/node_modules";

file_put_contents('.npmrc', "prefix = $global_nm_prefix");
file_put_contents('package.json', "{\"private\":true}");

$packages = [
    //compiler
    'webpack-cli',
    'webpack',
    '@babel/core',
    'babel-loader',
    '@babel/preset-react',
    '@babel/preset-env',
    '@babel/plugin-proposal-class-properties',
    '@babel/plugin-proposal-decorators',

    //dependencies
    'react',
    'react-dom',
//    'mobx',
//    'mobx-react',
//    'https://github.com/ninedays-io/fast-route',
   'google-protobuf',
];

run($yarn, array_merge(['add', '--modules-folder', $global_nm_dir, '--dev'], $packages));

$packages_clean = array_map(
    function ($e) {
        $parts = explode('@', $e);
        $e = strlen($parts[0]) ? $parts[0] : $e;
        if (preg_match('#https?://(.*)#', $e, $m)) $e = basename($e);
        return $e;
    },
    $packages
);

foreach ($packages_clean as $package) {
    run("cd $global_nm_dir/$package; $yarn link");
}

run($yarn, array_merge(['link'], $packages_clean));

$dev_tool = $mode == 'development' ? 'devtool: \'inline-source-map\',' : '';

$webpack_conf = <<<NOW
module.exports = {
    mode: '$mode',
    $dev_tool
    entry: './index.js',
    output: {
        path: require("path").join(__dirname, '/../web'),
        filename: 'main.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            '@babel/preset-react',
                            ['@babel/preset-env', {
                                targets: {
                                    "safari": "12"
                                }
                            }],
                         ],
                         plugins: [
                            ["@babel/plugin-proposal-decorators", { "legacy": true }],
                            "@babel/plugin-proposal-class-properties"
                        ]
                    }
                }
            }
        ]
    }
};
NOW;

file_put_contents('webpack.config.js', $webpack_conf);
