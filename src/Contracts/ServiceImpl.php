<?php

namespace Scalex\Contracts;

interface ServiceImpl
{
    public function call(string $route, string $method = 'GET', $data = [], $header = []);
}
