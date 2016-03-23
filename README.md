# HyperlinkAuth

[![Build Status](https://img.shields.io/travis/UseMuffin/HyperlinkAuth/master.svg?style=flat-square)](https://travis-ci.org/UseMuffin/HyperlinkAuth)
[![Coverage](https://img.shields.io/codecov/c/github/UseMuffin/HyperlinkAuth.svg?style=flat-square)](https://codecov.io/github/UseMuffin/HyperlinkAuth)
[![Total Downloads](https://img.shields.io/packagist/dt/muffin/hyperlinkauth.svg?style=flat-square)](https://packagist.org/packages/muffin/hyperlinkauth)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Password-less authentication for CakePHP 3.

Send a login hyperlink upon user's email submission on login page. 

1. User submits email
2. System sends link after validating email
3. User clicks link
4. System authenticates user after validating token

## Install

Using [Composer][composer]:

```
composer require muffin/hyperlinkauth:1.0.x-dev
```

You then need to load the plugin. You can use the shell command:

```
bin/cake plugin load Muffin/HyperlinkAuth
```

or by manually adding statement shown below to your app's `config/bootstrap.php`:

```php
Plugin::load('Muffin/HyperlinkAuth');
```

## Usage

```php
// src/Controller/AppController.php
public function initialize()
{
    $this->loadComponent('Auth', ['authenticate' => ['Muffin/HyperlinkAuth.Hyperlink']]);
}
```

And then create your login action:

```php
// src/Controller/UsersController.php
public function login()
{
    if (!$this->request->is('post') && !$this->request->is('token')) {
        return;
    }

    $user = $this->Auth->identify();

    if ($user === true) {
        $this->Flash->success(__('A one-time login URL has been emailed to you'));
        return;
    }

    if ($user) {
        $this->Auth->setUser($user);
        return $this->redirect($this->Auth->redirectUrl());
    }

    $this->Flash->error(__('Email is incorrect'), [
        'key' => 'auth'
    ]);
}
```

If you noticed, this is very similar to the [default way of doing things][1], with the difference
that it checks for a `token` type of request and handling `$user === true` (returned when email
is sent).

For sending the email, there are different approaches you can take. The simplest one (demonstrated 
here), uses the `UsersController` as the object listening to the `Auth.afterIdentify` event. A mailer
would be another way of handling that.

The code:

```php
// src/Controller/UsersController.php
public function implementedEvents()
{
    return parent::implementedEvents() + [
        'Auth.afterIdentify' => 'afterIdentify',
    ];
}

public function afterIdentify(Event $event, $result, HyperlinkAuthenticate $auth)
{
    if (!$this->request->is('post')) {
        return;
    }

    $token = $auth->token($result);

    $url = Router::url($this->Auth->config('loginAction') + ['?' => compact('token')], true);
    Email::deliver($result['email'], 'Login link', $url, ['from' => 'no-reply@' . env('HTTP_HOST')]);

    return true;
}
```

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of
their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

To ensure your PRs are considered for upstream, you MUST follow the [CakePHP coding standards][standards].

## Bugs & Feedback

http://github.com/usemuffin/hyperlinkauth/issues

## License

Copyright (c) 2016, [Use Muffin][muffin] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
[muffin]:http://usemuffin.com
[standards]:http://book.cakephp.org/3.0/en/contributing/cakephp-coding-conventions.html
[1]:http://book.cakephp.org/3.0/en/controllers/components/authentication.html#identifying-users-and-logging-them-in
