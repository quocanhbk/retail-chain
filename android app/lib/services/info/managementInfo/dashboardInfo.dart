import 'package:flutter/foundation.dart';

class DayRevenue {
  DateTime? _datetime;
  int? _revenue;

  DayRevenue(this._datetime, int? revenue) {
    if (revenue == null) {
      _revenue = 0;
    } else {
      _revenue = revenue;
    }
  }

  int? get revenue => _revenue;

  DateTime? get datetime => _datetime;
}

class MonthRevenue {
  DateTime? _dateTime;
  int? _revenue;

  MonthRevenue(this._dateTime, int? revenue) {
    if (revenue == null) {
      _revenue = 0;
    } else {
      _revenue = revenue;
    }
  }

  int? get revenue => _revenue;

  DateTime? get dateTime => _dateTime;
}

class DashboardInfo {
  int? _numberEmployee;
  int? _itemQuantities;
  int? _importFee;
  List<DayRevenue>? _sevenDaysRevenue;
  List<DayRevenue>? _monthRevenue;
  List<MonthRevenue>? _yearRevenue;
  int _revenueLastWeek = 0;
  int _revenueLastMonth = 0;
  int _revenueLastYear = 0;

  int? get numberEmployee => _numberEmployee;

  int? get itemQuantities => _itemQuantities;

  int? get importFee => _importFee;

  int get revenueLastWeek => _revenueLastWeek;

  DashboardInfo(
      {required String numberEmployee,
      required String itemQuantities,
      required String importFee,
      required List<DayRevenue> sevenDaysRevenue,
      required List<DayRevenue> monthRevenue,
      required List<MonthRevenue> yearRevenue}) {
    this._numberEmployee =
        int.tryParse(numberEmployee) == null ? 0 : int.tryParse(numberEmployee);
    this._itemQuantities =
        int.tryParse(itemQuantities) == null ? 0 : int.tryParse(itemQuantities);
    this._importFee =
        int.tryParse(importFee) == null ? 0 : int.tryParse(importFee);
    this._sevenDaysRevenue = sevenDaysRevenue;
    this._monthRevenue = monthRevenue;
    this._yearRevenue = yearRevenue;
    this._numberEmployee = int.parse(numberEmployee);
    _sevenDaysRevenue!.forEach((dayRevenue) {
      _revenueLastWeek += dayRevenue.revenue!;
    });
    _monthRevenue!.forEach((dayRevenue) {
      _revenueLastMonth += dayRevenue.revenue!;
    });
    _yearRevenue!.forEach((monthRevenue) {
      _revenueLastYear += monthRevenue.revenue!;
    });
  }

  List<MonthRevenue>? get yearRevenue => _yearRevenue;

  List<DayRevenue>? get monthRevenue => _monthRevenue;

  List<DayRevenue>? get sevenDaysRevenue => _sevenDaysRevenue;

  int get revenueLastMonth => _revenueLastMonth;

  List<int?> getRevenueWeekDayChart() {
    List<int?> chart = [];
    for (DayRevenue dayRevenue in _sevenDaysRevenue!) {
      chart.add(dayRevenue._revenue);
    }
    return chart;
  }

  List<int?> getRevenueMonthDayChart() {
    List<int?> chart = [];
    for (DayRevenue dayRevenue in _monthRevenue!) {
      chart.add(dayRevenue._revenue);
    }
    return chart;
  }

  List<int?> getRevenueYearDayChart() {
    List<int?> chart = [];
    for (MonthRevenue monthRevenue in _yearRevenue!) {
      chart.add(monthRevenue._revenue);
    }
    return chart;
  }

  int get revenueLastYear => _revenueLastYear;


}
