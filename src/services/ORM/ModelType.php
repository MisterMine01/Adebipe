<?php


namespace Api\Model;


enum ModelType {
    case INT;
    case STRING;
    case FLOAT;
    case BOOL;
    case BLOB;
}