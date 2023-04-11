# Nano ID package

#### Installation
```
composer require talanov/nanoid
```


#### Usage

```
use Talanov\Nanoid\HasNanoId;
use Talanov\Nanoid\NanoIdOptions;
...

class User ...
{
    use HasNanoId;
    ...
    
    public function getNanoIdOptions(): NanoIdOptions
    {
        return NanoIdOptions::make()
            ->length(8) // Optional. Default is 8. 
            ->saveTo('nano_id'); // Optional. Default is 'nano_id'.
    }
    
    ...
}
```

```
Schema::create('users', static function (Blueprint $table) {
    $table->id();
    $table->char('nano_id', 8)->unique();
    ...
});
```
