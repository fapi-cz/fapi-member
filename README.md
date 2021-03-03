Poznámky
========

## Datové struktury

### Členství

Členství je uloženo jako meta k uživateli pod klíčem v `FapiMembershipLoader::MEMBERSHIP_META_KEY`.
Struktura je následující:
~~~json
[
  {
    "level":  12, 
    "registered": "2020-01-01T20:00:01", 
    "until": "2020-01-01T20:00:01",
    "isUnlimited": true
  },
  {
      "level":  10, 
      "registered": "2020-02-01T20:00:01", 
      "until": "2020-04-01T20:00:01",
      "isUnlimited": false
  }    
]
~~~
