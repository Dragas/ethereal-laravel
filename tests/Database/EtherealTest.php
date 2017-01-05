<?php

use Ethereal\Database\Ethereal;
use Illuminate\Database\Eloquent\Collection;

class EtherealTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_keep_only_specified_relations_and_attributes()
    {
        $model = new Ethereal([
            'id' => 1,
            'email' => 'john@example.com',
        ]);

        $model->setRelation('test', collect());

        static::assertEquals(['id' => 1], $model->only('id')->toArray());
    }

    /**
     * @test
     */
    public function it_can_keep_all_attributes_and_relations_except_specified_ones()
    {
        $model = new Ethereal([
            'id' => 1,
            'email' => 'john@example.com',
        ]);

        $model->setRelation('test', collect());

        static::assertEquals(['id' => 1, 'test' => []], $model->except('email')->toArray());
    }

    /**
     * @test
     */
    public function it_can_set_attribute_without_morphing()
    {
        $model = new MorphEthereal;
        $model->setAttribute('email', 'test');

        self::assertEquals('not test', $model->email);

        $model->setRawAttribute('email', 'test');

        self::assertEquals('test', $model->email);
    }

    /**
     * @test
     */
    public function it_can_check_if_the_model_is_soft_deleting()
    {
        $model = new Ethereal;
        self::assertFalse($model->isSoftDeleting());

        $model = new MorphEthereal;
        self::assertTrue($model->isSoftDeleting());
    }

    /**
     * @test
     */
    public function it_can_check_if_attribute_is_present()
    {
        $model = new Ethereal;

        self::assertFalse($model->hasAttribute('title'));

        $model->setAttribute('title', 'test');
        self::assertTrue($model->hasAttribute('title'));

        $model->setAttribute('name', null);
        self::assertTrue($model->hasAttribute('name'));
    }

    /**
     * @test
     */
    public function it_can_check_if_all_attributes_are_present()
    {
        $model = new Ethereal;

        self::assertFalse($model->hasAttributes(['title']));

        $model->fill([
            'title' => 'test',
            'name' => null,
        ]);

        self::assertTrue($model->hasAttributes(['title']));
        self::assertFalse($model->hasAttributes(['email']));
        self::assertFalse($model->hasAttributes(['title', 'email']));
        self::assertTrue($model->hasAttributes(['title', 'name']));
    }

    /**
     * @test
     */
    public function it_can_check_if_one_of_the_attributes_is_present()
    {
        $model = new Ethereal;

        $model->fill([
            'title' => 'test',
            'name' => null,
        ]);

        self::assertTrue($model->hasAttributes(['title'], false));
        self::assertFalse($model->hasAttributes(['email'], false));
        self::assertTrue($model->hasAttributes(['title', 'email'], false));
        self::assertTrue($model->hasAttributes(['email', 'name'], false));
    }

    /**
     * @test
     */
    public function it_can_set_model_key()
    {
        $model = new Ethereal;
        $model->setKey(1);

        self::assertEquals(1, $model->getAttribute('id'));
    }

    /**
     * @test
     */
    public function it_can_fill_relations()
    {
        $model = new MorphEthereal([
            'user' => new TestProfileModel,
            'profile' => new TestProfileModel,
            'profiles' => new Collection([
                new TestProfileModel,
                new TestProfileModel,
            ]),
        ]);

        self::assertFalse($model->relationLoaded('user'));
        self::assertTrue($model->relationLoaded('profile'));
        self::assertTrue($model->relationLoaded('profiles'));
    }

    /**
     * @test
     */
    public function it_can_get_model_database_columns()
    {
        $model = new MorphEthereal;

        static::assertEquals(['id', 'title'], $model->getColumns());
    }

    /**
     * @test
     */
    public function it_can_set_model_database_columns()
    {
        $model = new MorphEthereal;
        static::assertEquals(['id', 'title'], $model->getColumns());

        $model->setColumns(['email', 'password']);
        static::assertEquals(['email', 'password'], $model->getColumns());
    }

    /**
     * @test
     */
    public function it_dirty_gets_only_column_values()
    {
        $model = new MorphEthereal([
            'name' => 'john',
            'title' => 'old',
        ]);
        $model->syncOriginal();

        self::assertEquals([], $model->getDirty());

        $model->name = 'doe';
        $model->id = 2;

        self::assertEquals(['id' => 2], $model->getDirty());

        $model->title = 'new';

        self::assertEquals([
            'title' => 'new',
            'id' => 2,
        ], $model->getDirty());
    }
}

class MorphEthereal extends Ethereal
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $columns = ['id', 'title'];

    protected $relationships = ['profile', 'profiles'];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = 'not ' . $value;
    }

    public function profile()
    {
        return $this->hasOne(TestProfileModel::class, 'user_id');
    }

    public function profiles()
    {
        return $this->hasMany(TestProfileModel::class, 'user_id');
    }
}
