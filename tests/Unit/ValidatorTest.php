<?php

use Ptk\Validator\Validator;
use Ptk\Validator\Types;

beforeEach(function(){
    $this->validator = new Validator();
});

test('required(true): pass', function () {
    expect($this->validator->required()->validate('hello world!'))->toBeTrue();
});

test('required(true): fail (empty)', function () {
    expect($this->validator->required()->validate(''))->toBeFalse();
});

test('required(true): fail (null)', function () {
    expect($this->validator->required()->validate(null))->toBeFalse();
});

test('empty(true): pass (empty string)', function () {
    expect($this->validator->empty()->validate(''))->toBeTrue();
});

test('empty(true): pass (not empty string)', function () {
    expect($this->validator->empty()->validate('wow!!!'))->toBeTrue();
});

test('empty(false): pass (not empty string)', function () {
    expect($this->validator->empty(false)->validate('wow!!!'))->toBeTrue();
});

test('empty(false): fail (empty string)', function () {
    expect($this->validator->empty(false)->validate(''))->toBeFalse();
});

test('empty(true): pass (empty array)', function () {
    expect($this->validator->empty()->validate([]))->toBeTrue();
});

test('empty(true): pass (not empty array)', function () {
    expect($this->validator->empty()->validate(['wow!!!']))->toBeTrue();
});

test('empty(false): pass (not empty array)', function () {
    expect($this->validator->empty(false)->validate(['wow!!!']))->toBeTrue();
});

test('empty(false): fail (empty array)', function () {
    expect($this->validator->empty(false)->validate([]))->toBeFalse();
});

test('empty(true): fail (null)', function () {
    expect($this->validator->empty(true)->validate(null))->toBeFalse();
});

test('empty(false): fail (null)', function () {
    expect($this->validator->empty(false)->validate(null))->toBeFalse();
});

test('nullable(true): pass (null)', function(){
    expect($this->validator->nullable(true)->validate(null))->toBeTrue();
});

test('nullable(true): pass (not null)', function(){
    expect($this->validator->nullable(true)->validate('hey?!'))->toBeTrue();
});

test('nullable(false): fail (null)', function(){
    expect($this->validator->nullable(false)->validate(null))->toBeFalse();
});

test('nullable(false): pass (not null)', function(){
    expect($this->validator->nullable(false)->validate('hey?!'))->toBeTrue();
});

test('type(bool): pass', function(){
    expect($this->validator->type(Types::BOOL)->validate(true))->toBeTrue();
});

test('type(numeric): pass', function(){
    expect($this->validator->type(Types::NUMERIC)->validate(1))->toBeTrue();
});

test('type(int): pass', function(){
    expect($this->validator->type(Types::INT)->validate(3))->toBeTrue();
});

test('type(float): pass', function(){
    expect($this->validator->type(Types::FLOAT)->validate(3.14))->toBeTrue();
});

test('type(string): pass', function(){
    expect($this->validator->type(Types::STRING)->validate('uhu!'))->toBeTrue();
});

test('type(array): pass', function(){
    expect($this->validator->type(Types::ARRAY)->validate([]))->toBeTrue();
});

test('type(object): pass', function(){
    expect($this->validator->type(Types::OBJECT)->validate(new stdClass()))->toBeTrue();
});

test('type(resource): pass', function(){
    expect($this->validator->type(Types::RESOURCE)->validate(STDIN))->toBeTrue();
});

test('type(callable): pass', function(){
    expect($this->validator->type(Types::CALLABLE)->validate(function(){}))->toBeTrue();
});

test('type(null): fail', function(){
    expect($this->validator->type(Types::BOOL)->validate(null))->toBeFalse();
});

test('is(className): pass', function(){
    expect($this->validator->is(Validator::class)->validate($this->validator))->toBeTrue();
});

test('is(className): fail', function(){
    expect($this->validator->is(stdClass::class)->validate($this->validator))->toBeFalse();
});

test('min(int): pass', function(){
    expect($this->validator->min(1)->validate(10))->toBeTrue();
});

test('min(float): pass', function(){
    expect($this->validator->min(3.14)->validate(3.15))->toBeTrue();
});

test('min(string|string): pass', function(){
    expect($this->validator->min('abc')->validate('abcde'))->toBeTrue();
});

test('min(string|int): pass', function(){
    expect($this->validator->min(3)->validate('abcde'))->toBeTrue();
});

test('min(DateTime): pass', function(){
    expect($this->validator->min(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeTrue();
});


test('min(int): fail', function(){
    expect($this->validator->min(10)->validate(5))->toBeFalse();
});

test('min(float): fail', function(){
    expect($this->validator->min(3.14)->validate(3.13))->toBeFalse();
});

test('min(string|int): fail', function(){
    expect($this->validator->min(10)->validate('abcde'))->toBeFalse();
});

test('min(string|string): fail', function(){
    expect($this->validator->min('abcdefghij')->validate('abcde'))->toBeFalse();
});

test('min(DateTime): fail', function(){
    expect($this->validator->min(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeFalse();
});




test('max(int): pass', function(){
    expect($this->validator->max(10)->validate(3))->toBeTrue();
});

test('max(float): pass', function(){
    expect($this->validator->max(3.14)->validate(3.13))->toBeTrue();
});

test('max(string|int): pass', function(){
    expect($this->validator->max(10)->validate('abcde'))->toBeTrue();
});

test('max(string|string): pass', function(){
    expect($this->validator->max('abcdefgh')->validate('abcde'))->toBeTrue();
});

test('max(DateTime): pass', function(){
    expect($this->validator->max(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeTrue();
});

test('max(int): fail', function(){
    expect($this->validator->max(10)->validate(15))->toBeFalse();
});

test('max(float): fail', function(){
    expect($this->validator->max(3.14)->validate(3.15))->toBeFalse();
});

test('max(string|int): fail', function(){
    expect($this->validator->max(3)->validate('abcde'))->toBeFalse();
});

test('max(string|string): fail', function(){
    expect($this->validator->max('abc')->validate('abcde'))->toBeFalse();
});

test('max(DateTime): fail', function(){
    expect($this->validator->max(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeFalse();
});

test('between(int): pass', function(){
    expect($this->validator->between(1, 5)->validate(3))->toBeTrue();
});

test('between(float): pass', function(){
    expect($this->validator->between(1.1, 1.5)->validate(1.3))->toBeTrue();
});

test('between(string|int): pass', function(){
    expect($this->validator->between(5, 10)->validate('abcdefgh'))->toBeTrue();
});

test('between(string|string): pass', function(){
    expect($this->validator->between('abc', 'abcdefghijkl')->validate('abcdefgh'))->toBeTrue();
});

test('between(DateTime): pass', function(){
    expect($this->validator->between(new DateTimeImmutable('1981-01-01'), new DateTimeImmutable('1981-12-31'))->validate(new DateTimeImmutable('1981-05-12')))->toBeTrue();
});

test('between(int): fail', function(){
    expect($this->validator->between(1, 5)->validate(8))->toBeFalse();
});

test('between(float): fail', function(){
    expect($this->validator->between(1.1, 1.5)->validate(1.9))->toBeFalse();
});

test('between(string|int): fail', function(){
    expect($this->validator->between(5, 10)->validate('abcdefghijklmnopqrst'))->toBeFalse();
});

test('between(string|string): fail', function(){
    expect($this->validator->between('abc', 'abcdefghijkl')->validate('ab'))->toBeFalse();
});

test('between(DateTime): fail', function(){
    expect($this->validator->between(new DateTimeImmutable('1981-01-01'), new DateTimeImmutable('1981-12-31'))->validate(new DateTimeImmutable('1980-05-12')))->toBeFalse();
});

test('between(down, up diff type): fail', function(){
    $this->validator->between(new DateTimeImmutable('1981-01-01'), 1)->validate(new DateTimeImmutable('1980-05-12'));
})->throws(\RuntimeException::class);

test('contains(string): pass', function(){
    expect($this->validator->contains('cde')->validate('abcdefghi'))->toBeTrue();
});

test('contains(string): fail', function(){
    expect($this->validator->contains('xyz')->validate('abcdefghi'))->toBeFalse();
});

test('contains(array): pass', function(){
    expect($this->validator->contains(['ab', 'cde', 'fgh'])->validate('cde'))->toBeTrue();
});

test('contains(array): fail', function(){
    expect($this->validator->contains(['ab', 'cd', 'ef'])->validate('xyz'))->toBeFalse();
});

test('file(): pass', function(){
    expect($this->validator->file()->validate(__FILE__))->toBeTrue();
});

test('file(): fail', function(){
    expect($this->validator->file()->validate('fake.file'))->toBeFalse();
});

test('directory(): pass', function(){
    expect($this->validator->directory()->validate(__DIR__))->toBeTrue();
});

test('directory(): fail', function(){
    expect($this->validator->directory()->validate(__FILE__))->toBeFalse();
});

test('exists(): pass', function(){
    expect($this->validator->exists()->validate(__DIR__))->toBeTrue();
});

test('exists(): fail', function(){
    expect($this->validator->exists()->validate('not.exists'))->toBeFalse();
});

test('startswith(): pass', function(){
    expect($this->validator->startswith('abc')->validate('abcdefg'))->toBeTrue();
});

test('startswith(): fail', function(){
    expect($this->validator->startswith('bcd')->validate('abcdefg'))->toBeFalse();
});

test('endswith(): pass', function(){
    expect($this->validator->endswith('efg')->validate('abcdefg'))->toBeTrue();
});

test('endswith(): fail', function(){
    expect($this->validator->endswith('def')->validate('abcdefg'))->toBeFalse();
});

test('result(): pass', function(){
    $this->validator->endswith('efg')->startswith('bc')->validate('abcdefg');
    expect($this->validator->result())->toMatchArray(['endswith' => true, 'startswith' => false]);
});

test('passed(): pass', function(){
    $this->validator->endswith('efg')->startswith('bc')->validate('abcdefg');
    expect($this->validator->passed())->toMatchArray(['endswith']);
});

test('failed(): pass', function(){
    $this->validator->endswith('efg')->startswith('bc')->validate('abcdefg');
    expect($this->validator->failed())->toMatchArray(['startswith']);
});