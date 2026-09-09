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

test('leq(numeric): pass', function(){
    expect($this->validator->lessOrEqual(10)->validate(9))->toBeTrue();
});

test('leq(numeric): fail', function(){
    expect($this->validator->lessOrEqual(9)->validate(10))->toBeFalse();
});

test('leq(int, str): pass', function(){
    expect($this->validator->lessOrEqual(10)->validate('abcde'))->toBeTrue();
});

test('leq(int, str): fail', function(){
    expect($this->validator->lessOrEqual(3)->validate('abcde'))->toBeFalse();
});

test('leq(str, str): pass', function(){
    expect($this->validator->lessOrEqual('abcdefg')->validate('abcde'))->toBeTrue();
});

test('leq(str, str): fail', function(){
    expect($this->validator->lessOrEqual('abcd')->validate('abcde'))->toBeFalse();
});

test('leq(int, array): pass', function(){
    expect($this->validator->lessOrEqual(3)->validate([1, 2]))->toBeTrue();
});

test('leq(int, array): fail', function(){
    expect($this->validator->lessOrEqual(3)->validate([1,2,3,4]))->toBeFalse();
});

test('leq(array, array): pass', function(){
    expect($this->validator->lessOrEqual([1,2,3,4])->validate([1, 2]))->toBeTrue();
});

test('leq(array, array): fail', function(){
    expect($this->validator->lessOrEqual([1,2,3])->validate([1,2,3,4]))->toBeFalse();
});

test('leq(DateTime): pass', function(){
    expect($this->validator->lessOrEqual(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeTrue();
});

test('leq(DateTime): fail', function(){
    expect($this->validator->lessOrEqual(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeFalse();
});

test('less(numeric): pass', function(){
    expect($this->validator->less(10)->validate(9))->toBeTrue();
});

test('less(numeric): fail', function(){
    expect($this->validator->less(9)->validate(10))->toBeFalse();
});

test('less(int, str): pass', function(){
    expect($this->validator->less(10)->validate('abcde'))->toBeTrue();
});

test('less(int, str): fail', function(){
    expect($this->validator->less(3)->validate('abcde'))->toBeFalse();
});

test('less(str, str): pass', function(){
    expect($this->validator->less('abcdefg')->validate('abcde'))->toBeTrue();
});

test('less(str, str): fail', function(){
    expect($this->validator->less('abcd')->validate('abcde'))->toBeFalse();
});

test('less(int, array): pass', function(){
    expect($this->validator->less(3)->validate([1, 2]))->toBeTrue();
});

test('less(int, array): fail', function(){
    expect($this->validator->less(3)->validate([1,2,3,4]))->toBeFalse();
});

test('less(array, array): pass', function(){
    expect($this->validator->less([1,2,3,4])->validate([1, 2]))->toBeTrue();
});

test('less(array, array): fail', function(){
    expect($this->validator->less([1,2,3])->validate([1,2,3,4]))->toBeFalse();
});

test('less(DateTime): pass', function(){
    expect($this->validator->less(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeTrue();
});

test('less(DateTime): fail', function(){
    expect($this->validator->less(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeFalse();
});

test('geq(numeric): pass', function(){
    expect($this->validator->greatOrEqual(9)->validate(10))->toBeTrue();
});

test('geq(numeric): fail', function(){
    expect($this->validator->greatOrEqual(10)->validate(9))->toBeFalse();
});

test('geq(int, str): pass', function(){
    expect($this->validator->greatOrEqual(3)->validate('abcde'))->toBeTrue();
});

test('geq(int, str): fail', function(){
    expect($this->validator->greatOrEqual(10)->validate('abcde'))->toBeFalse();
});

test('geq(str, str): pass', function(){
    expect($this->validator->greatOrEqual('abcde')->validate('abcdefg'))->toBeTrue();
});

test('geq(str, str): fail', function(){
    expect($this->validator->greatOrEqual('abcde')->validate('abcd'))->toBeFalse();
});

test('geq(int, array): pass', function(){
    expect($this->validator->greatOrEqual(3)->validate([1, 2, 3, 4]))->toBeTrue();
});

test('geq(int, array): fail', function(){
    expect($this->validator->greatOrEqual(3)->validate([1,2]))->toBeFalse();
});

test('geq(array, array): pass', function(){
    expect($this->validator->greatOrEqual([1,2])->validate([1,2,3,4]))->toBeTrue();
});

test('geq(array, array): fail', function(){
    expect($this->validator->greatOrEqual([1,2,3,4])->validate([1,2,3]))->toBeFalse();
});

test('geq(DateTime): pass', function(){
    expect($this->validator->greatOrEqual(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeTrue();
});

test('geq(DateTime): fail', function(){
    expect($this->validator->greatOrEqual(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeFalse();
});




test('great(numeric): pass', function(){
    expect($this->validator->great(9)->validate(10))->toBeTrue();
});

test('great(numeric): fail', function(){
    expect($this->validator->great(10)->validate(9))->toBeFalse();
});

test('great(int, str): pass', function(){
    expect($this->validator->great(3)->validate('abcde'))->toBeTrue();
});

test('great(int, str): fail', function(){
    expect($this->validator->great(10)->validate('abcde'))->toBeFalse();
});

test('great(str, str): pass', function(){
    expect($this->validator->great('abcde')->validate('abcdefg'))->toBeTrue();
});

test('great(str, str): fail', function(){
    expect($this->validator->great('abcde')->validate('abcd'))->toBeFalse();
});

test('great(int, array): pass', function(){
    expect($this->validator->great(3)->validate([1, 2, 3, 4]))->toBeTrue();
});

test('great(int, array): fail', function(){
    expect($this->validator->great(3)->validate([1,2]))->toBeFalse();
});

test('great(array, array): pass', function(){
    expect($this->validator->great([1,2])->validate([1,2,3,4]))->toBeTrue();
});

test('great(array, array): fail', function(){
    expect($this->validator->great([1,2,3,4])->validate([1,2,3]))->toBeFalse();
});

test('great(DateTime): pass', function(){
    expect($this->validator->great(new DateTimeImmutable('1981-05-12'))->validate(new DateTimeImmutable('now')))->toBeTrue();
});

test('great(DateTime): fail', function(){
    expect($this->validator->great(new DateTimeImmutable('now'))->validate(new DateTimeImmutable('1981-05-12')))->toBeFalse();
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
    expect($this->validator->contains('cde')->validate(['ab', 'cde', 'fgh']))->toBeTrue();
});

test('contains(array): fail', function(){
    expect($this->validator->contains('xyz')->validate(['ab', 'cde', 'fgh']))->toBeFalse();
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