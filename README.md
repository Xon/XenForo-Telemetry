# XenForo-Telemetry

Datadog-based telemetry

# IO, cache & database query counters

Add to the end of library/config.php

```
SV_Telemetry_Wrapper::injectForIO($config);
SV_Telemetry_Wrapper::injectForDatabase($config);
SV_Telemetry_Wrapper::injectForCache($config);
```

XenForo-SessionCache is supported if it uses the same cache handler as the main cache