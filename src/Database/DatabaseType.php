<?php

namespace Vista\Database;

enum DatabaseType: string
{
    case MYSQL = 'mysql';
    case SQLITE = 'sqlite';
}
