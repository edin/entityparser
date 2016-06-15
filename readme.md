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

##You can find out more by executing following commands:

```cmd
**> php demo.php parse Blog --type**

Types:
  Type: UserId -> int
  Type: CommentId -> int
  Type: PostId -> int
  Type: Email, Length: 100 -> string
  Type: ShortString, Length: 100 -> string
  Type: LongString, Length: 500 -> string
  Type: DateModified -> date
  Type: DateCreated -> date
  Type: PasswordHash, Length: 100 -> string
```

```cmd
> php demo.php parse Blog --const

Constants:
  Constant: unitNamespace = app\data
  Constant: unitDir = blog
  Constant: unitName = BlogDbContext
```

```cmd
**> php demo.php parse Blog --entity**

Entities:
  Entity: User
    Field: Id -> UserId
    Field: FirstName -> ShortString
    Field: LastName -> ShortString
    Field: Email -> Email
    Field: UserName -> ShortString
    Field: PasswordHash -> ShortString
    Field: Modified -> DateModified
    Field: Created -> DateCreated

  Entity: Comment
    Field: Id -> CommentId
    Field: UserId -> UserId
    Field: PostId -> PostId
    Field: Text -> Text
    Field: Modified -> DateModified
    Field: Created -> DateCreated

  Entity: Post
    Field: Id -> PostId
    Field: UserId -> UserId
    Field: Title -> ShortString
    Field: Content -> Text
    Field: Modified -> DateModified
    Field: Created -> DateCreated
```


```
**> php demo.php parse Blog --sql**
```

```sql
CREATE TABLE `User` (
    `Id` INT,
    `FirstName` VARCHAR(100),
    `LastName` VARCHAR(100),
    `Email` VARCHAR(100),
    `UserName` VARCHAR(100),
    `PasswordHash` VARCHAR(100),
    `Modified` DATE,
    `Created` DATE
);

CREATE TABLE `Comment` (
    `Id` INT,
    `UserId` INT,
    `PostId` INT,
    `Text` MEDIUMTEXT,
    `Modified` DATE,
    `Created` DATE
);

CREATE TABLE `Post` (
    `Id` INT,
    `UserId` INT,
    `Title` VARCHAR(100),
    `Content` MEDIUMTEXT,
    `Modified` DATE,
    `Created` DATE
);
```

```
**> php demo.php parse Blog --php**
```

```php
<?php
class User
{
  public $Id;
  public $FirstName;
  public $LastName;
  public $Email;
  public $UserName;
  public $PasswordHash;
  public $Modified;
  public $Created;
}

class Comment
{
  public $Id;
  public $UserId;
  public $PostId;
  public $Text;
  public $Modified;
  public $Created;
}

class Post
{
  public $Id;
  public $UserId;
  public $Title;
  public $Content;
  public $Modified;
  public $Created;
}
```