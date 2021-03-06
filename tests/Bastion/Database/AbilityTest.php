<?php

use Ethereal\Bastion\Bastion;
use Ethereal\Bastion\Database\Ability;
use Ethereal\Bastion\Database\Role;
use Ethereal\Bastion\Store;
use Illuminate\Support\Collection;

class AbilityTest extends BaseTestCase
{
    use UsesDatabase;

    /**
     * @test
     */
    public function it_can_create_ability()
    {
        $this->migrate();

        Ability::createAbilityRecord('test');
        self::assertEquals(1, Ability::where([
            'name' => 'test',
            'entity_id' => null,
            'entity_type' => null,
        ])->count());

        Ability::createAbilityRecord('test', '*');
        self::assertEquals(1, Ability::where([
            'name' => 'test',
            'entity_id' => null,
            'entity_type' => '*',
        ])->count());

        Ability::createAbilityRecord('test', TestUserModel::class);
        self::assertEquals(1, Ability::where([
            'name' => 'test',
            'entity_id' => null,
            'entity_type' => TestUserModel::class,
        ])->count());

        Ability::createAbilityRecord('test', TestUserModel::class, 1);
        self::assertEquals(1, Ability::where([
            'name' => 'test',
            'entity_id' => 1,
            'entity_type' => TestUserModel::class,
        ])->count());

        Ability::createAbilityRecord('test', new TestUserModel(['id' => 2]));
        self::assertEquals(1, Ability::where([
            'name' => 'test',
            'entity_id' => 2,
            'entity_type' => TestUserModel::class,
        ])->count());
    }

    /**
     * @test
     */
    public function it_can_collect_abilities_from_names()
    {
        $this->migrate();

        $abilities = Ability::collectAbilities(['create', 'destroy']);

        static::assertArraySubset([
            [
                'name' => 'create',
            ],
            [
                'name' => 'destroy',
            ],
        ], $abilities->values()->toArray());
    }

    /**
     * @test
     */
    public function it_can_collect_abilities_from_names_with_custom_attributes()
    {
        $this->migrate();

        $abilities = Ability::collectAbilities(['create' => ['title' => 'Create'], 'destroy']);

        static::assertArraySubset([
            [
                'name' => 'create',
                'title' => 'Create',
            ],
            [
                'name' => 'destroy',
            ],
        ], $abilities->values()->toArray());
    }

    /**
     * @test
     */
    public function it_can_collect_abilities_by_id()
    {
        $this->migrate();

        $ability = Ability::createAbilityRecord('create');
        $abilities = Ability::collectAbilities([$ability->getKey(), 'destroy']);

        static::assertArraySubset([
            [
                'name' => 'create',
            ],
            [
                'name' => 'destroy',
            ],
        ], $abilities->values()->toArray());
    }

    /**
     * @test
     */
    public function it_can_collect_abilities_with_models()
    {
        $this->migrate();

        $ability = Ability::createAbilityRecord('create');
        $abilities = Ability::collectAbilities([$ability, 'destroy']);

        static::assertArraySubset([
            [
                'name' => 'create',
            ],
            [
                'name' => 'destroy',
            ],
        ], $abilities->values()->toArray());
    }

    /**
     * @test
     */
    public function it_can_save_abilities_with_models()
    {
        $this->migrate();

        $ability = new Ability(['name' => 'create']);
        $abilities = Ability::collectAbilities([$ability, 'destroy']);

        static::assertArraySubset([
            [
                'name' => 'create',
            ],
            [
                'name' => 'destroy',
            ],
        ], $abilities->values()->toArray());
        self::assertTrue($ability->exists);
    }

    /**
     * @test
     */
    public function it_collects_abilities_and_returns_keyed_collection()
    {
        $this->migrate();

        $ability1 = Ability::create(['name' => 'create']);
        $ability2 = Ability::create(['name' => 'destroy']);
        $abilities = Ability::collectAbilities([$ability1, $ability2]);

        static::assertArraySubset([
            $ability1->getKey() => [
                'id' => $ability1->getKey(),
                'name' => 'create',
            ],
            $ability2->getKey() => [
                'id' => $ability2->getKey(),
                'name' => 'destroy',
            ],
        ], $abilities->toArray());
    }

    /**
     * @test
     */
    public function it_can_find_ability()
    {
        $this->migrate();

        $created = Ability::createAbilityRecord('create');
        $ability = Ability::findAbility('create');

        static::assertArraySubset($created->toArray(), $ability->toArray());
    }

    /**
     * @test
     */
    public function it_can_find_ability_for_model()
    {
        $this->migrate();

        $model = new TestUserModel(['id' => 0]);
        $created = Ability::createAbilityRecord('create', $model);

        $ability = Ability::findAbility('create', $model);
        static::assertArraySubset($created->toArray(), $ability->toArray());

        $ability = Ability::findAbility('create', TestUserModel::class, $model->getKey());
        static::assertArraySubset($created->toArray(), $ability->toArray());
    }

    /**
     * @test
     */
    public function it_can_get_abilities_for_authority()
    {
        $this->migrate();

        $store = new Store;
        $bastion = new Bastion($this->app, $store);

        $randomUser = TestUserModel::create(['email' => 'john@example.com']);
        $user = TestUserModel::create(['email' => 'jane@example.com']);

        $bastion->allow($randomUser)->to('dance');
        $bastion->allow($user)->to(['kick', 'punch']);

        self::assertEquals(2, Ability::getAbilities($user)->count());
        self::assertEquals(['kick', 'punch'], Ability::getAbilities($user)->pluck('name')->all());
    }

    /**
     * @test
     */
    public function it_can_get_abilities_for_authority_and_his_roles()
    {
        $this->migrate();

        $store = new Store;
        $bastion = new Bastion($this->app, $store);

        $role = Role::create(['name' => 'admin']);
        $bastion->allow($role)->to('sit');

        $randomUser = TestUserModel::create(['email' => 'john@example.com']);
        $bastion->assign('admin')->to($randomUser);
        $bastion->allow($randomUser)->to('dance');

        $user = TestUserModel::create(['email' => 'jane@example.com']);
        $bastion->assign('admin')->to($user);
        $bastion->allow($user)->to(['kick', 'punch']);

        $roles = new Collection([$role]);

        self::assertEquals(3, Ability::getAbilities($user, $roles)->count());
        self::assertEquals(['kick', 'punch', 'sit'], Ability::getAbilities($user, $roles)->pluck('name')->all());
    }

    /**
     * @test
     */
    public function it_has_identifier()
    {
        $this->migrate();

        $store = new Store;
        $bastion = new Bastion($this->app, $store);

        $role = Role::create(['name' => 'admin']);
        $bastion->allow($role)->to('sit');

        self::assertEquals('sit', Ability::findAbility('sit')->identifier);

        $bastion->allow($role)->to('stand', TestUserModel::class);
        self::assertEquals('stand-testusermodel', Ability::findAbility('stand', TestUserModel::class)->identifier);

        $bastion->allow($role)->to('crouch', TestUserModel::class, 1);
        self::assertEquals('crouch-testusermodel-1', Ability::findAbility('crouch', TestUserModel::class, 1)->identifier);

        $bastion->allow($role)->everything();
        self::assertEquals('*-*', Ability::findAbility('*', '*')->identifier);
    }

    /**
     * @test
     */
    public function it_has_identifier_with_parent_when_retrieved_from_store()
    {
        $this->migrate();

        $store = new Store;
        $bastion = new Bastion($this->app, $store);

        $role = Role::create(['name' => 'admin']);
        $bastion->allow($role)->parent(TestUserModel::class, 2)->to('sit');

        self::assertEquals('sit-testusermodel-2', $store->getAbilities($role)->first(function ($item) {
            return $item->name === 'sit';
        })->identifier);

        $bastion->allow($role)->parent(TestUserModel::class, 2)->to('stand', TestUserModel::class);
        self::assertEquals('stand-testusermodel-testusermodel-2', $store->getAbilities($role)->first(function ($item) {
            return $item->name === 'stand';
        })->identifier);

        $bastion->allow($role)->parent(TestUserModel::class, 2)->to('crouch', TestUserModel::class, 1);
        self::assertEquals('crouch-testusermodel-1-testusermodel-2', $store->getAbilities($role)->first(function ($item) {
            return $item->name === 'crouch';
        })->identifier);

        $bastion->allow($role)->parent(TestUserModel::class, 2)->everything();
        self::assertEquals('*-*-testusermodel-2', $store->getAbilities($role)->first(function ($item) {
            return $item->name === '*';
        })->identifier);
    }

    /**
     * @test
     */
    public function it_can_generate_ability_identifiers()
    {
        self::assertEquals(['kick-*', '*-*'], Ability::compileAbilityIdentifiers('kick', '*'));
        self::assertEquals(['kick', '*-*', '*'], Ability::compileAbilityIdentifiers('kick'));
        self::assertEquals(
            ['kick-testusermodel', 'kick-*', '*-testusermodel', '*-*', 'kick-testusermodel-1', '*-testusermodel-1'],
            Ability::compileAbilityIdentifiers('kick', new TestUserModel(['id' => 1]))
        );
        self::assertEquals(
            ['kick-testusermodel', 'kick-*', '*-testusermodel', '*-*', 'kick-testusermodel-1', '*-testusermodel-1'],
            Ability::compileAbilityIdentifiers('kick', new TestUserModel(['id' => 1]))
        );
        self::assertEquals(
            [
                'kick-testusermodel-testusermodel-2',
                'kick-*-testusermodel-2',
                '*-testusermodel-testusermodel-2',
                '*-*-testusermodel-2',
                'kick-testusermodel-1-testusermodel-2',
                '*-testusermodel-1-testusermodel-2',
            ],
            Ability::compileAbilityIdentifiers('kick', new TestUserModel(['id' => 1]), new TestUserModel(['id' => 2]))
        );
    }

    protected function assertContainsValues($expected, $array)
    {
        self::assertEquals(count($expected), count(array_intersect($expected, $array)));
    }
}
