# Change Log

## [v5.6.0](https://github.com/prooph/event-sourcing/tree/v5.6.0)

[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.5.0...v5.6.0)

**Implemented enhancements:**

- add metadata to create stream [\#81](https://github.com/prooph/event-sourcing/pull/81) ([hiddeb](https://github.com/hiddeb))

**Closed issues:**

- Keep/add the aggregate in the indentity map after saving [\#78](https://github.com/prooph/event-sourcing/issues/78)

**Merged pull requests:**

- Added .gitattributes file [\#80](https://github.com/prooph/event-sourcing/pull/80) ([Brammm](https://github.com/Brammm))

## [v5.5.0](https://github.com/prooph/event-sourcing/tree/v5.5.0) (2018-05-10)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.4.0...v5.5.0)

**Implemented enhancements:**

- add option to disable identity map [\#77](https://github.com/prooph/event-sourcing/pull/77) ([prolic](https://github.com/prolic))

## [v5.4.0](https://github.com/prooph/event-sourcing/tree/v5.4.0) (2018-04-30)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.3.0...v5.4.0)

**Implemented enhancements:**

- Allow to add a custom event store to AggregateRepository [\#76](https://github.com/prooph/event-sourcing/pull/76) ([Orkin](https://github.com/Orkin))

**Closed issues:**

- Move 'aggregateId' up [\#74](https://github.com/prooph/event-sourcing/issues/74)

**Merged pull requests:**

- Update documentation on aggregate root inheritance [\#75](https://github.com/prooph/event-sourcing/pull/75) ([fritz-gerneth](https://github.com/fritz-gerneth))

## [v5.3.0](https://github.com/prooph/event-sourcing/tree/v5.3.0) (2017-12-17)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.2.0...v5.3.0)

**Implemented enhancements:**

- add more tests [\#72](https://github.com/prooph/event-sourcing/pull/72) ([prolic](https://github.com/prolic))
- test php 7.2 on travis [\#71](https://github.com/prooph/event-sourcing/pull/71) ([prolic](https://github.com/prolic))
- Add static type hint to docblock [\#70](https://github.com/prooph/event-sourcing/pull/70) ([jdrieghe](https://github.com/jdrieghe))

**Merged pull requests:**

- Restructure docs [\#69](https://github.com/prooph/event-sourcing/pull/69) ([codeliner](https://github.com/codeliner))

## [v5.2.0](https://github.com/prooph/event-sourcing/tree/v5.2.0) (2017-06-21)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.1...v5.2.0)

**Implemented enhancements:**

- AggregateType in RepositoryFactory [\#64](https://github.com/prooph/event-sourcing/issues/64)
- remove map iterator [\#67](https://github.com/prooph/event-sourcing/pull/67) ([prolic](https://github.com/prolic))
- use custom aggregate type mapping [\#65](https://github.com/prooph/event-sourcing/pull/65) ([prolic](https://github.com/prolic))

## [v5.1](https://github.com/prooph/event-sourcing/tree/v5.1) (2017-05-24)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.0.1...v5.1)

**Implemented enhancements:**

- Extract aggregate root into traits to make it easier to avoid domain extending infrastructure [\#62](https://github.com/prooph/event-sourcing/pull/62) ([Xerkus](https://github.com/Xerkus))

**Merged pull requests:**

- minor corrections - README.md [\#63](https://github.com/prooph/event-sourcing/pull/63) ([geekcom](https://github.com/geekcom))

## [v5.0.1](https://github.com/prooph/event-sourcing/tree/v5.0.1) (2017-05-09)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.0.0...v5.0.1)

**Merged pull requests:**

- Bump snapshot store dependency to stable [\#61](https://github.com/prooph/event-sourcing/pull/61) ([Xerkus](https://github.com/Xerkus))

## [v5.0.0](https://github.com/prooph/event-sourcing/tree/v5.0.0) (2017-03-30)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.0.0-beta3...v5.0.0)

**Closed issues:**

- Aggregates not automatically saving [\#59](https://github.com/prooph/event-sourcing/issues/59)
- New beta release [\#58](https://github.com/prooph/event-sourcing/issues/58)

**Merged pull requests:**

- Docs [\#60](https://github.com/prooph/event-sourcing/pull/60) ([prolic](https://github.com/prolic))

## [v5.0.0-beta3](https://github.com/prooph/event-sourcing/tree/v5.0.0-beta3) (2017-03-14)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.0.0-beta2...v5.0.0-beta3)

**Implemented enhancements:**

- Delete snapshot read model [\#55](https://github.com/prooph/event-sourcing/pull/55) ([prolic](https://github.com/prolic))
- change EventStore::load method [\#50](https://github.com/prooph/event-sourcing/pull/50) ([prolic](https://github.com/prolic))
- remove snapshot store [\#49](https://github.com/prooph/event-sourcing/pull/49) ([prolic](https://github.com/prolic))

**Fixed bugs:**

- Snapshot args incorrect [\#51](https://github.com/prooph/event-sourcing/issues/51)
- fix snapshotting [\#52](https://github.com/prooph/event-sourcing/pull/52) ([prolic](https://github.com/prolic))

**Merged pull requests:**

- a few cs thingies [\#56](https://github.com/prooph/event-sourcing/pull/56) ([basz](https://github.com/basz))
- Psr [\#54](https://github.com/prooph/event-sourcing/pull/54) ([basz](https://github.com/basz))
- lets use an available util class [\#53](https://github.com/prooph/event-sourcing/pull/53) ([basz](https://github.com/basz))

## [v5.0.0-beta2](https://github.com/prooph/event-sourcing/tree/v5.0.0-beta2) (2017-01-12)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v5.0.0-beta1...v5.0.0-beta2)

**Implemented enhancements:**

- Remove JsonSerializable mock [\#44](https://github.com/prooph/event-sourcing/issues/44)
- Remove configurable aggregate translator [\#46](https://github.com/prooph/event-sourcing/pull/46) ([prolic](https://github.com/prolic))

**Fixed bugs:**

- Exceptions will be thrown if aggregate has no pending events [\#42](https://github.com/prooph/event-sourcing/issues/42)

**Closed issues:**

- Remove ConfigurableAggregateTranslator? [\#43](https://github.com/prooph/event-sourcing/issues/43)

**Merged pull requests:**

- Travis config improvement [\#48](https://github.com/prooph/event-sourcing/pull/48) ([oqq](https://github.com/oqq))
- adds early return if no pending event is present [\#47](https://github.com/prooph/event-sourcing/pull/47) ([oqq](https://github.com/oqq))
- Remove JsonSerializable Mock objects for User and UserNameChanged [\#45](https://github.com/prooph/event-sourcing/pull/45) ([sandrokeil](https://github.com/sandrokeil))
- Add docs [\#41](https://github.com/prooph/event-sourcing/pull/41) ([prolic](https://github.com/prolic))

## [v5.0.0-beta1](https://github.com/prooph/event-sourcing/tree/v5.0.0-beta1) (2016-12-13)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v4.0...v5.0.0-beta1)

**Implemented enhancements:**

- Change event handling methods [\#33](https://github.com/prooph/event-sourcing/issues/33)
- Add JsonSerializable Mock objects for User and UserNameChanged [\#39](https://github.com/prooph/event-sourcing/pull/39) ([sandrokeil](https://github.com/sandrokeil))
- remove determine handler method from aggregate root [\#36](https://github.com/prooph/event-sourcing/pull/36) ([prolic](https://github.com/prolic))
- Updates [\#35](https://github.com/prooph/event-sourcing/pull/35) ([prolic](https://github.com/prolic))
- update event sourcing for new interface [\#34](https://github.com/prooph/event-sourcing/pull/34) ([prolic](https://github.com/prolic))
- Support for PHP 7.1 [\#31](https://github.com/prooph/event-sourcing/pull/31) ([prolic](https://github.com/prolic))
- update to use coverall 1.0 [\#27](https://github.com/prooph/event-sourcing/pull/27) ([prolic](https://github.com/prolic))

**Fixed bugs:**

- fix quickstart with -\>getParam\('recordedEvents', new \ArrayIterator\(\)\) [\#29](https://github.com/prooph/event-sourcing/pull/29) ([prolic](https://github.com/prolic))

**Closed issues:**

- Update to coveralls ^1.0 [\#24](https://github.com/prooph/event-sourcing/issues/24)

**Merged pull requests:**

- Use static instead of self to allow inheritance [\#40](https://github.com/prooph/event-sourcing/pull/40) ([sandrokeil](https://github.com/sandrokeil))
- Allow basic event store interface in repository [\#38](https://github.com/prooph/event-sourcing/pull/38) ([codeliner](https://github.com/codeliner))
- moves ramsey/uuid dependency to require-dev [\#30](https://github.com/prooph/event-sourcing/pull/30) ([oqq](https://github.com/oqq))
- Minor changes [\#26](https://github.com/prooph/event-sourcing/pull/26) ([malukenho](https://github.com/malukenho))
- rhumsaa/uuid package name has changed to ramsey/uuid [\#25](https://github.com/prooph/event-sourcing/pull/25) ([jpkleemans](https://github.com/jpkleemans))

## [v4.0](https://github.com/prooph/event-sourcing/tree/v4.0) (2015-11-22)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v4.0-beta.1...v4.0)

**Implemented enhancements:**

- Add method extractVersion to AggregateTranslator [\#15](https://github.com/prooph/event-sourcing/issues/15)
- allow empty payload in occur-method [\#19](https://github.com/prooph/event-sourcing/pull/19) ([prolic](https://github.com/prolic))
- apply early :\) [\#17](https://github.com/prooph/event-sourcing/pull/17) ([prolic](https://github.com/prolic))
- extract aggregate version [\#16](https://github.com/prooph/event-sourcing/pull/16) ([prolic](https://github.com/prolic))

**Closed issues:**

- Document new "apply events late behavior" [\#11](https://github.com/prooph/event-sourcing/issues/11)

**Merged pull requests:**

- v4.0 [\#22](https://github.com/prooph/event-sourcing/pull/22) ([codeliner](https://github.com/codeliner))
- Update quickstart [\#21](https://github.com/prooph/event-sourcing/pull/21) ([codeliner](https://github.com/codeliner))
- allow empty payload in occur-method [\#20](https://github.com/prooph/event-sourcing/pull/20) ([prolic](https://github.com/prolic))
- Align translator and decorator to fix replay bug [\#18](https://github.com/prooph/event-sourcing/pull/18) ([codeliner](https://github.com/codeliner))

## [v4.0-beta.1](https://github.com/prooph/event-sourcing/tree/v4.0-beta.1) (2015-10-21)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v3.0...v4.0-beta.1)

**Implemented enhancements:**

- fix namespace organisation in tests [\#13](https://github.com/prooph/event-sourcing/pull/13) ([prolic](https://github.com/prolic))
- update according to https://github.com/prooph/event-store/pull/112 [\#12](https://github.com/prooph/event-sourcing/pull/12) ([prolic](https://github.com/prolic))

**Closed issues:**

- Add snapshot strategy [\#1](https://github.com/prooph/event-sourcing/issues/1)

**Merged pull requests:**

- event store 6.0-beta dep [\#14](https://github.com/prooph/event-sourcing/pull/14) ([codeliner](https://github.com/codeliner))
- Adjustments for prooph/event-store v6 [\#10](https://github.com/prooph/event-sourcing/pull/10) ([codeliner](https://github.com/codeliner))

## [v3.0](https://github.com/prooph/event-sourcing/tree/v3.0) (2015-09-08)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v3.0-beta.2...v3.0)

## [v3.0-beta.2](https://github.com/prooph/event-sourcing/tree/v3.0-beta.2) (2015-08-28)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v3.0-beta.1...v3.0-beta.2)

**Implemented enhancements:**

- Adjust for Event-Store 5.0 + add tests [\#8](https://github.com/prooph/event-sourcing/pull/8) ([prolic](https://github.com/prolic))

**Closed issues:**

- Update Prooph/Common dependency version to ^3 [\#3](https://github.com/prooph/event-sourcing/issues/3)

**Merged pull requests:**

- test php7 on travis [\#9](https://github.com/prooph/event-sourcing/pull/9) ([prolic](https://github.com/prolic))
- Cleanup .php\_cs config file [\#7](https://github.com/prooph/event-sourcing/pull/7) ([prolic](https://github.com/prolic))
- Fix php-cs for all files in repo [\#6](https://github.com/prooph/event-sourcing/pull/6) ([prolic](https://github.com/prolic))
- Add php-cs-fixer [\#5](https://github.com/prooph/event-sourcing/pull/5) ([prolic](https://github.com/prolic))

## [v3.0-beta.1](https://github.com/prooph/event-sourcing/tree/v3.0-beta.1) (2015-08-05)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v2.1...v3.0-beta.1)

**Merged pull requests:**

- v3.0  [\#4](https://github.com/prooph/event-sourcing/pull/4) ([codeliner](https://github.com/codeliner))
- Update composer.json [\#2](https://github.com/prooph/event-sourcing/pull/2) ([prolic](https://github.com/prolic))

## [v2.1](https://github.com/prooph/event-sourcing/tree/v2.1) (2015-05-09)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v2.0.2...v2.1)

## [v2.0.2](https://github.com/prooph/event-sourcing/tree/v2.0.2) (2015-05-04)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/2.0.1...v2.0.2)

## [2.0.1](https://github.com/prooph/event-sourcing/tree/2.0.1) (2015-05-02)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v2.0...2.0.1)

## [v2.0](https://github.com/prooph/event-sourcing/tree/v2.0) (2015-05-01)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/v1.0.0...v2.0)

## [v1.0.0](https://github.com/prooph/event-sourcing/tree/v1.0.0) (2014-09-28)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/0.2.0...v1.0.0)

## [0.2.0](https://github.com/prooph/event-sourcing/tree/0.2.0) (2014-09-07)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/0.1.1...0.2.0)

## [0.1.1](https://github.com/prooph/event-sourcing/tree/0.1.1) (2014-07-05)
[Full Changelog](https://github.com/prooph/event-sourcing/compare/0.1.0...0.1.1)

## [0.1.0](https://github.com/prooph/event-sourcing/tree/0.1.0) (2014-06-08)


\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
