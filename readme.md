# About

Parser for simple entity definition file.

Definition file example:

```
const unitNamespace = "app\data";
const unitDir       = "blog";
const unitName      = "BlogDbContext";

@PK type UserId     int;
@PK type CommentId     int;
@PK type PostId     int;

@Unique
type Email         string(100);

type ShortString   string(100);
type LongString    string(500);
type DateModified  date;
type DateCreated   date;
type PasswordHash  string(100);

entity User {
    UserId        Id;
    ShortString   FirstName;
    ShortString   LastName;
    Email         Email;
    ShortString   UserName;
    ShortString   PasswordHash;
    DateModified  Modified;
    DateCreated   Created;
}

entity Comment {
    CommentId     Id;
    UserId        UserId;
    PostId        PostId;
    Text          Text;
    DateModified  Modified;
    DateCreated   Created;
}

entity Post {
    PostId        Id;
    UserId        UserId;
    ShortString   Title;
    Text          Content;
    DateModified  Modified;
    DateCreated   Created;    
}
```

Following snipet demonstrates how to load definition file.
```php
<?php
use EntityParser\Parser\Parser;
use EntityParser\Parser\Contract\CodeModelInterface;

$parser = new Parser();
$codeModel = $parser->parse(file_get_content("Blog.edf"));

/**
 * @var CodeModelInterface $codeModel
 */
$entities  = $codeModel->getEntities();
$types     = $codeModel->getTypes();
$constants = $codeModel->getConstants();
 
```