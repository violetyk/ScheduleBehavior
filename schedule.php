<?php
class ScheduleBehavior extends ModelBehavior {

	var $columnStatus = 'status'; // ステータスカラム名
	var $statusEnable = 1; // 有効と判定するステータス
	var $columnStart  = 'published'; // 公開開始日時カラム(datetime型)
	var $columnEnd    = 'unpublished'; // 公開終了日時カラム（datetime型)

	function setup(&$model, $config = array()) {
		$this->_set($config);
	}

	function cleanup(&$model) {
		parent::cleanup($model);
	}

	function beforeFind(&$model, $query) {
		$query['conditions']["$model->name.$this->columnStatus"] = $this->statusEnable;
		$query['conditions']["$model->name.$this->columnStart <= ?"] = date("Y-m-d H:i:s");
		$query['conditions']["$model->name.$this->columnEnd >= ?"] = date("Y-m-d H:i:s");
		return $query;
	}

	function beforeSave(&$model) {

		if(isset($model->_schema[$this->columnStart]) && $model->_schema[$this->columnStart]['type'] == 'datetime') {
			/*
			 * 公開開始日時の補完
			 *  日付指定なし                -> 現在日時
			 *  日付指定有り・日時指定なし  -> 指定日付 00:00:00
			 *  日付指定有り・日時指定有り  -> 指定日付 指定日時
			 */
			if(empty($model->data[$model->name][$this->columnStart.'_date'])) {
				$model->data[$model->name][$this->columnStart] = date('Y-m-d H:i:s');
			} else {
				if(empty($model->data[$model->name][$this->columnStart.'_time'])
					|| strtotime($model->data[$model->name][$this->columnStart.'_time']) === false ) {
					$model->data[$model->name][$this->columnStart] = 
						sprintf("%s 00:00:00", $model->data[$model->name][$this->columnStart.'_date']);
				} else {
					$model->data[$model->name][$this->columnStart] =
						sprintf("%s %s", $model->data[$model->name][$this->columnStart.'_date'], $model->data[$model->name][$this->columnStart.'_time']);
				}
			}
		}

		if(isset($model->_schema[$this->columnEnd]) && $model->_schema[$this->columnEnd]['type'] == 'datetime') {
			/*
			 * unpublishedの補完
			 *  日付指定なし                -> 2099-12-31 23:59:59
			 *  日付指定有り・日時指定なし  -> 指定日付 23:59:59
			 *  日付指定有り・日時指定有り  -> 指定日付 指定日時
			 */
			if(empty($model->data[$model->name][$this->columnEnd.'date'])) {
				$model->data[$model->name][$this->columnEnd] = '2099-12-31 23:59:59';
			} else {
				if(empty($model->data[$model->name][$this->columnEnd.'_time'])
					|| strtotime($model->data[$model->name][$this->columnEnd.'_time']) === false ) {
					$model->data[$model->name][$this->columnEnd] = 
						sprintf("%s 23:59:59", $model->data[$model->name][$this->columnEnd.'_date']);
				} else {
					$model->data[$model->name][$this->columnEnd] =
						sprintf("%s %s", $model->data[$model->name][$this->columnEnd.'_date'], $model->data[$model->name][$this->columnEnd.'_time']);
				}
			}
		}
	}

}
