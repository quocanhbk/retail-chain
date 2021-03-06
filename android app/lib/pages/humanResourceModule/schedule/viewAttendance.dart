import 'package:bkrm/services/info/hrInfo/attendanceInfo.dart';
import 'package:bkrm/services/info/hrInfo/scheduleInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';
import 'package:intl/intl.dart';

class ViewAttendancePage extends StatefulWidget {
  ScheduleInfo schedule;
  ViewAttendancePage(this.schedule);

  @override
  _ViewAttendancePageState createState() => _ViewAttendancePageState();
}

class _ViewAttendancePageState extends State<ViewAttendancePage> {
  final PagingController<int, Map<String, dynamic>> _pagingController =
      PagingController(firstPageKey: 0);

  DateTime fromDate = DateTime.now();
  DateTime toDate = DateTime.now();

  void initState() {
    _pagingController.addPageRequestListener((pageKey) {
      _fetchPage(pageKey);
    });
    super.initState();
  }

  Future<void> _fetchPage(int pageKey) async {
    try {
      debugPrint("Pagekey " + pageKey.toString());
      debugPrint("Fetch page");
      bool isLastPage = false;
      List<Map<String, dynamic>> returnResult;
      if (DateTime.now()
              .subtract(Duration(days: (pageKey + 1) * 30))
              .compareTo(widget.schedule.scheduleStartDate!) <
          0) {
        if (DateTime.now()
                .subtract(Duration(days: pageKey * 30))
                .compareTo(widget.schedule.scheduleStartDate!) >=
            0) {
          debugPrint("Last page");
          returnResult = await BkrmService().getAttendancesFromDateToDate(
                  widget.schedule.scheduleStartDate!,
                  DateTime.now().subtract(Duration(days: pageKey * 30)),
                  scheduleId: widget.schedule.scheduleId);
          isLastPage = true;
        } else {
          debugPrint("Over last page");
          return;
        }
      } else {
        debugPrint("Not last page");
        returnResult = await BkrmService().getAttendancesFromDateToDate(
                DateTime.now().subtract(Duration(days: (pageKey + 1) * 30)),
                DateTime.now().subtract(Duration(days: pageKey * 30)),
                scheduleId: widget.schedule.scheduleId);
      }
      if (isLastPage) {
        _pagingController.appendLastPage(returnResult.reversed.toList());
      } else {
        final nextPageKey = pageKey + 1;
        _pagingController.appendPage(
            returnResult.reversed.toList(), nextPageKey);
      }
    } catch (error) {
      _pagingController.error = error;
    }
  }

  String getNameOfWeekDay(DateTime date) {
    String weekday;
    switch (DateFormat('EEEE').format(date)) {
      case "Monday":
        weekday = "Th??? Hai";
        break;
      case "Tuesday":
        weekday = "Th??? Ba";
        break;
      case "Wednesday":
        weekday = "Th??? T??";
        break;
      case "Thursday":
        weekday = "Th??? N??m";
        break;
      case "Friday":
        weekday = "Th??? S??u";
        break;
      case "Saturday":
        weekday = "Th??? B???y";
        break;
      case "Sunday":
        weekday = "Ch??? Nh???t";
        break;
      default:
        weekday = "";
    }
    return weekday;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Ng??y l??m vi???c"),
      ),
      body: Container(
        padding: EdgeInsets.all(16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Center(
              child: Text(
                "Nh??n vi??n: " + widget.schedule.name!,
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
              ),
            ),
            SizedBox(
              height: 30,
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Expanded(
                    flex: 3,
                    child: Text(
                      "Ng??y",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                    )),
                Expanded(
                  flex: 1,
                  child: Center(
                    child: Text(
                      "C?? m???t",
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                    ),
                  ),
                )
              ],
            ),
            Divider(),
            Expanded(
              child: PagedListView<int, Map<String, dynamic>>.separated(
                shrinkWrap: true,
                pagingController: _pagingController,
                builderDelegate:
                    PagedChildBuilderDelegate<Map<String, dynamic>>(
                        itemBuilder: (context, item, index) {
                  AttendanceInfo attendance =
                      item["attendance"] as AttendanceInfo;
                  return Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Expanded(
                          flex: 3,
                          child: Text(item["date"].toString() +
                              " (" +
                              getNameOfWeekDay(
                                  DateTime.parse(item["date"].toString())) +
                              ")")),
                      Expanded(
                        flex: 1,
                        child: Checkbox(
                          value: attendance.date != null,
                          onChanged: (bool? value) {},
                        ),
                      )
                    ],
                  );
                },
                        noItemsFoundIndicatorBuilder: (context){
                          return Container(
                            height: MediaQuery.of(context).size.height/2,
                            child: Center(
                              child: Text("Kh??ng c?? ng??y ??i???m danh",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w300,color: Colors.grey),),
                            ),
                          );
                        },
                      firstPageErrorIndicatorBuilder: (context) {
                        return Container(
                          height: MediaQuery.of(context).size.height / 2,
                          child: Center(
                            child: Text(
                              "???? c?? l???i x???y ra",
                              style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w300,
                                  color: Colors.red),
                            ),
                          ),
                        );
                      },),
                separatorBuilder: (BuildContext context, int index) {
                  return Divider();
                },
              ),
            )
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _pagingController.dispose();
    super.dispose();
  }
}
