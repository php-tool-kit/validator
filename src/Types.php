<?php

namespace Ptk\Validator;

enum Types
{
    case BOOL;
    case NUMERIC;
    case INT;
    case FLOAT;
    case STRING;
    case ARRAY;
    case OBJECT;
    case RESOURCE;
    case CALLABLE;
}