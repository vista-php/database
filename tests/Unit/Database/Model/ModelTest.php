<?php

namespace Tests\Unit\Database\Model;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Vista\Custom\Collection;
use Vista\Model\Model;
use Vista\QueryBuilder\Factory;
use Vista\QueryBuilder\QueryBuilder;
use Vista\QueryBuilder\QueryBuilderFactory;

class ModelTest extends TestCase
{
    private QueryBuilder|MockInterface $queryBuilder;
    private Factory $qbFactory;

    public function setUp(): void
    {
        $this->queryBuilder = m::mock(QueryBuilder::class);
        $this->queryBuilder->shouldReceive('setModelClass')->with(TestModel::class);
        $this->queryBuilder->shouldReceive('setPrimaryKey')->with('column1');
        $this->queryBuilder->shouldReceive('select')->with(['column1', 'column2'])->andReturn($this->queryBuilder);
        $this->queryBuilder->shouldReceive('from')->with('tests')->andReturn($this->queryBuilder);

        $this->qbFactory = new QueryBuilderFactory($this->queryBuilder);
        $this->queryBuilder = $this->qbFactory->create();
        TestModel::setQueryBuilderFactory($this->qbFactory);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testSelectWithAsterisk()
    {
        $result = TestModel::select();

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testSelectWithColumns()
    {
        $this->queryBuilder->shouldReceive('select')
            ->once()
            ->with(['column1'])
            ->andReturn($this->queryBuilder);
        $result = TestModel::select(['column1']);
        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testLeftJoin()
    {
        $this->queryBuilder->shouldReceive('leftJoin')
            ->once()
            ->with('table', 'primaryKey', 'foreignKey', '=')
            ->andReturn($this->queryBuilder);

        $result = TestModel::leftJoin('table', 'primaryKey', 'foreignKey', '=');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testRightJoin()
    {
        $this->queryBuilder->shouldReceive('rightJoin')
            ->with('table', 'primaryKey', 'foreignKey', '=')
            ->andReturn($this->queryBuilder);

        $result = TestModel::rightJoin('table', 'primaryKey', 'foreignKey', '=');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testInnerJoin()
    {
        $this->queryBuilder->shouldReceive('innerJoin')
            ->with('table', 'primaryKey', 'foreignKey', '=')
            ->andReturn($this->queryBuilder);

        $result = TestModel::innerJoin('table', 'primaryKey', 'foreignKey', '=');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testWhere()
    {
        $this->queryBuilder->shouldReceive('where')
            ->with('column', '=', 'value')
            ->andReturn($this->queryBuilder);

        $result = TestModel::where('column', '=', 'value');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testOrWhere()
    {
        $this->queryBuilder->shouldReceive('orWhere')
            ->with('column', '=', 'value')
            ->andReturn($this->queryBuilder);

        $result = TestModel::orWhere('column', '=', 'value');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testOrderBy()
    {
        $this->queryBuilder->shouldReceive('orderBy')
            ->with('column', 'DESC')
            ->andReturn($this->queryBuilder);

        $result = TestModel::orderBy('column', 'DESC');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testLimit()
    {
        $this->queryBuilder->shouldReceive('limit')
            ->with(10, 0)
            ->andReturn($this->queryBuilder);

        $result = TestModel::limit(10, 0);

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testGroupBy()
    {
        $this->queryBuilder->shouldReceive('groupBy')
            ->with('column')
            ->andReturn($this->queryBuilder);

        $result = TestModel::groupBy('column');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testHaving()
    {
        $this->queryBuilder->shouldReceive('having')
            ->with('column', '=', 'value')
            ->andReturn($this->queryBuilder);

        $result = TestModel::having('column', '=', 'value');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testOrHaving()
    {
        $this->queryBuilder->shouldReceive('orHaving')
            ->with('column', '=', 'value')
            ->andReturn($this->queryBuilder);

        $result = TestModel::orHaving('column', '=', 'value');

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testUpdate()
    {
        $this->queryBuilder->shouldReceive('update')
            ->with('tests', ['column' => 'value'])
            ->andReturn($this->queryBuilder);

        $result = TestModel::update(['column' => 'value']);

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testDelete()
    {
        $this->queryBuilder->shouldReceive('delete')
            ->andReturn($this->queryBuilder);

        $result = TestModel::delete();

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }

    public function testCreate()
    {
        $testModel = new TestModel($this->qbFactory);
        $testModel->column1 = 'value1';
        $testModel->column2 = 'value2';
        $this->queryBuilder->shouldReceive('insert')
            ->with('tests', ['column1' => 'value1', 'column2' => 'value2'])
            ->andReturn($this->queryBuilder);
        $this->queryBuilder->shouldReceive('save')
            ->andReturn(true);
        $this->queryBuilder->shouldReceive('from')
            ->with('tests')
            ->andReturn($this->queryBuilder);
        $this->queryBuilder->shouldReceive('lastInsertId')
            ->andReturn(1);
        $this->queryBuilder->shouldReceive('find')
            ->with(1)
            ->andReturn($testModel);

        $result = TestModel::insert(['column1' => 'value1', 'column2' => 'value2']);

        $this->assertEquals($testModel, $result);
    }

    public function testFirst()
    {
        $testModel = new TestModel($this->qbFactory);
        $testModel->column1 = 'value1';
        $testModel->column2 = 'value2';
        $this->queryBuilder->shouldReceive('first')
            ->andReturn($testModel);

        $result = TestModel::first();

        $this->assertEquals($testModel, $result);
    }

    public function testLast()
    {
        $testModel = new TestModel($this->qbFactory);
        $testModel->column1 = 'value1';
        $testModel->column2 = 'value2';
        $this->queryBuilder->shouldReceive('last')
            ->andReturn($testModel);

        $result = TestModel::last();

        $this->assertEquals($testModel, $result);
    }

    public function testFind()
    {
        $testModel = new TestModel($this->qbFactory);
        $testModel->column1 = 'value1';
        $testModel->column2 = 'value2';
        $this->queryBuilder->shouldReceive('find')
            ->with(1)
            ->andReturn($testModel);

        $result = TestModel::find(1);

        $this->assertEquals($testModel, $result);
    }

    public function testAll()
    {
        $this->queryBuilder->shouldReceive('all')
            ->andReturn(new Collection(['column1' => 'value1', 'column2' => 'value2']));

        $result = TestModel::all();

        $this->assertEquals(new Collection(['column1' => 'value1', 'column2' => 'value2']), $result);
    }

    public function testCount()
    {
        $this->queryBuilder->shouldReceive('count')
            ->andReturn(1);

        $result = TestModel::count();

        $this->assertEquals(1, $result);
    }

    public function testExists()
    {
        $this->queryBuilder->shouldReceive('exists')
            ->andReturn(true);

        $result = TestModel::exists();

        $this->assertTrue($result);
    }

    public function testSaveInsert()
    {
        $this->queryBuilder->shouldReceive('save')
            ->andReturn(true);
        $this->queryBuilder->shouldReceive('insert')
            ->andReturn($this->queryBuilder);
        $this->queryBuilder->shouldReceive('lastInsertId')
            ->andReturn(4);

        $model = new TestModel($this->qbFactory);
        $model->column2 = 5;

        $result = $model->save();

        $this->assertInstanceOf(TestModel::class, $result);
    }

    public function testSaveUpdate()
    {
        $this->queryBuilder->shouldReceive('save')
            ->andReturn(true);
        $this->queryBuilder->shouldReceive('update')
            ->andReturn($this->queryBuilder);

        $this->queryBuilder->shouldReceive('where')
            ->with('column1', 5)
            ->andReturn($this->queryBuilder);

        $model = new TestModel($this->qbFactory);
        $model->column1 = 1;
        $model->column2 = 5;

        $result = $model->save();

        $this->assertInstanceOf(TestModel::class, $result);
    }
}

class TestModel extends Model
{
    protected string $table = 'tests';

    protected string $primaryKey = 'column1';
    protected array $columns = ['column1', 'column2'];
}
