# PHPBench Blackfire Extension

This repository contains a PHPBench Blackfire extension, which enables you to profile your benchmarks using [Blackfire.io].
The extension contains a custom executor and logger. The executor can be used without the logger, the logger cannot be used without the executor. 

Please note that the logger is using *Premium/Enterprise* features of blackfire, usage of the logger is optional.

## Installation

PHPBench requires extensions to be installed via composer since it is using the autoloader to load the extension classes.
Besides that this extension is building its autoloader to load additional classes that are used to connect with Blackfire
during the execution of your benchmarks. Therefore the only possible installation method is composer. 

To install this extension execute.

`composer require phpbench/phpbench jaapio/phpbench-blackfire`

The extension requires the [Blackfire agent] to instrument the executed benchmarks like every other implementation
that uses blackfire.io. 

## Configuration

To enable the extension you add the extension to your PHPBench config. The extension will pick the
settings from the PHPBench config as shown in the example below. `config` and `env` are both optional.

`env` can be omitted when your Blackfire account doesn't have an [environment]. 

`config` is a path to a Blackfire ini file containing the client id and client token to authenticate with Blackfire api. When
`config` setting is omitted the users home directory is consulted for a config file. The last fallback are the environment
variables `BLACKFIRE_CLIENT_ID`, `BLACKFIRE_CLIENT_TOKEN` and `BLACKFIRE_ENDPOINT`   

```json
{
  "bootstrap": "vendor/autoload.php",
  "path": "tests/benchmark",
  "extensions": [
    "Jaapio\\Blackfire\\Extension"
  ],
  "blackfire" : {
    "config": 'path/to/blackfire.ini',
    "env": "<id>"
  }
}
```

## Execution

To run full-featured build you can execute the command below.
`phpbench run -l blackfire --executor=blackfire --tag="Build_name"`

### Refs

PHPBench allows you to set refs on each benchmark. The refs are translated into steps on a profile report and visible in Blackfire.

### Assertions *(Premium/Enterprise)*

Blackfire has options to add assertions. This extension supports these assertions in the executor annotation that can be
set above your benchmark. The expressions are not parsed by the extension but just passed as-is. Please consult the Blackfire
documentation for more information about assertions.


```
class YourBench
{

    /**
     * @Executor(
     *     "blackfire",
     *     assertions={
     *      {"expression"="main.peak_memory < 11kb", "title"="memory peak"},
     *      "main.wall_time < 1ms"
     *      }
     * )
     */
    public function benchMd5() : void
    {
        md5('test');
    }
```

### Iterations & Variants

Each execution of a benchmark, so iterations and variants included are sent as separate profiles to Blackfire.
Be aware that the number of profiles created by this extension can be huge when using it in the wrong way. I do not recommend using large amounts of iterations in combination with a lot of benchmarks. It will most likely flood your Blackfire account.

[Blackfire.io]: https://blackfire.io
[Blackfire agent]: https://blackfire.io/docs/up-and-running/installation
[environment]: https://blackfire.io/docs/reference-guide/environments
