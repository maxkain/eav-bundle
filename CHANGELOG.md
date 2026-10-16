# CHANGELOG for 1.1

- `EavQueryFactory` is refactored, it becomes more readable and extensible.
- Selecting attribute's values and sorting entities by attribute's values features are added.
- Improved filtering by value's id and value's title for enum attributes.

# CHANGELOG for 2.0

- A lot of refactoring and improvements.
- Tests created, the main scenarios are covered.
- [BC] `orValueLogic` parameter of `EavQueryFactory` is true by default now.
- [BC] Changed `EavTag` options syntax.
- [BC] `EavInverter` returns `InverterOutput` object now.
- [BC] May be other breaking changes. Check documentation and your code before update.
- `EavTag` query condition high speed up.
- `OrphanedEavsCleaner` added. Batch processing and affected data improvements. 
- Orphaned EAVs removing on invert with `addOnly` added.
