import 'package:bkrm/pages/humanResourceModule/shift/addNewShiftPage.dart';
import 'package:bkrm/pages/humanResourceModule/shift/detailShiftPage.dart';
import 'package:bkrm/services/info/hrInfo/shiftInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListShiftPage extends StatefulWidget {
  @override
  _ListShiftPageState createState() => _ListShiftPageState();
}

class _ListShiftPageState extends State<ListShiftPage> {
  GlobalKey<RefreshIndicatorState> _refreshKey= GlobalKey();
  List<ShiftInfo>? listShifts;
  initState() {
    super.initState();
    getAllShift();
  }

  Future<bool> getAllShift() async {
    List<ShiftInfo>? listShift = await BkrmService().getShifts();
    if (listShift != null) {
      this.listShifts = listShift;
    } else {
      this.listShifts = [];
    }
    setState(() {});
    return true;
  }

  List<Widget> buildListShift() {
    List<Widget> listCardShift = [];
    if (listShifts == null) {
      return [];
    }
    listShifts!.forEach((element) {
      String activeDays = "";
      if (element.monday!) {
        activeDays += "Thứ Hai - ";
      }
      if (element.tuesday!) {
        activeDays += "Thứ Ba - ";
      }
      if (element.wednesday!) {
        activeDays += "Thứ Tư - ";
      }
      if (element.thursday!) {
        activeDays += "Thứ Năm - ";
      }
      if (element.friday!) {
        activeDays += "Thứ Sáu - ";
      }
      if (element.saturday!) {
        activeDays += "Thứ Bảy - ";
      }
      if (element.sunday!) {
        activeDays += "Chủ Nhật - ";
      }
      activeDays = activeDays.substring(0, activeDays.length - 3);
      listCardShift.add(InkWell(
        onTap: () async{
          final schedules = await BkrmService().getSchedules(shiftId: element.shiftId);
          final result = await Navigator.push(context, PageTransition(child: DetailShiftPage(element,schedules), type: pageTransitionType));
          debugPrint(result.toString());
          if(result!=null){
            if(result){
              if(_refreshKey.currentState!=null){
                _refreshKey.currentState!.show();
              }else{
                setState(() {
                  getAllShift();
                });
              }

              // getAllShift().then((value){
              //   _refreshKey.currentState.show()
              // });
            }
          }
        },
        child: Card(
          elevation: 5.0,
          child: Padding(
            padding: const EdgeInsets.all(8.0),
            child: Column(
              children: [
                Center(
                  child: Text(
                    element.name!,
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
                SizedBox(
                  height: 10,
                ),
                Center(
                  child: Text(
                    activeDays,
                    textAlign: TextAlign.center,
                  ),
                ),
                SizedBox(
                  height: 10,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Container(
                            alignment: Alignment.centerLeft,
                            child: Text("Bắt đầu:" +
                                DateFormat("HH:mm:ss")
                                    .format(element.startTime!)))),
                    Expanded(
                        flex: 1,
                        child: Container(
                            alignment: Alignment.centerRight,
                            child: Text("Kết thúc:" +
                                DateFormat("HH:mm:ss")
                                    .format(element.endTime!)))),
                  ],
                ),
                SizedBox(
                  height: 10,
                ),
                Row(
                  children: [
                    Expanded(
                        flex: 1,
                        child: Container(
                            alignment: Alignment.centerLeft,
                            child: Text("Ngày bắt đầu:" +
                                DateFormat("dd-MM-yyyy")
                                    .format(element.startDate!)))),
                    element.endDate == null
                        ? Expanded(
                            flex: 1,
                            child: Container(
                                alignment: Alignment.centerRight,
                                child: Text("Ngày kết thúc: __/__/____")))
                        : Expanded(
                            flex: 1,
                            child: Container(
                                alignment: Alignment.centerRight,
                                child: Text("Ngày kết thúc:" +
                                    DateFormat("dd-MM-yyyy")
                                        .format(element.endDate!)))),
                  ],
                ),
                SizedBox(
                  height: 10,
                ),
              ],
            ),
          ),
        ),
      ));
    });

    listCardShift.add(InkWell(
      onTap: () {
        Navigator.push(context, PageTransition(child: AddNewShiftPage(), type: pageTransitionType)).then((value) async {
          await getAllShift();
        });
      },
      child: Container(
        alignment: Alignment.center,
        height: 90,
        width: MediaQuery.of(context).size.width,
        decoration: BoxDecoration(
            color: Colors.white38,
            border: Border.all(width: 2, color: Colors.grey)),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Center(
            child: Icon(
              Icons.add,
              color: Colors.grey,
            ),
          ),
          Center(
              child: Text(
            "Thêm ca làm việc",
            style: TextStyle(
                fontSize: 16, fontWeight: FontWeight.w300, color: Colors.grey),
          )),
        ]),
      ),
    ));
    return listCardShift;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Ca làm việc"),
      ),
      drawer: ExpansionDrawer(this.context),
      body: Padding(
        padding: EdgeInsets.all(
            8.0),
        child: RefreshIndicator(
          key: _refreshKey,
          onRefresh: () async {
            await getAllShift();
          },
          child: SingleChildScrollView(
            child: Container(
                child: listShifts == null
                    ? Center(
                        child: CircularProgressIndicator(),
                      )
                    : Column(
                        children: buildListShift(),
                      )),
          ),
        ),
      ),
    );
  }
}
