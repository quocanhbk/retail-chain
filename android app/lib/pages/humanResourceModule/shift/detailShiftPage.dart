import 'package:bkrm/pages/humanResourceModule//schedule/addNewSchedulePage.dart';
import 'package:bkrm/pages/humanResourceModule//schedule/detailSchedulePage.dart';
import 'package:bkrm/services/info/hrInfo/attendanceInfo.dart';
import 'package:bkrm/services/info/hrInfo/scheduleInfo.dart';
import 'package:bkrm/services/info/hrInfo/shiftInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';
import 'package:weekday_selector/weekday_selector.dart';

import 'package:bkrm/pages/Nav2App.dart';

class DetailShiftPage extends StatefulWidget {
  final ShiftInfo shift;
  List<ScheduleInfo>? schedules = [];
  DetailShiftPage(this.shift, this.schedules);

  @override
  _DetailShiftPageState createState() => _DetailShiftPageState();
}

class _DetailShiftPageState extends State<DetailShiftPage> {
  final _formKey = GlobalKey<FormState>();

  bool isEdit = false;

  bool needRefresh = false;

  late List<bool?> weekdays;

  DateTime? startTime;
  DateTime? endTime;
  DateTime? startDate;
  DateTime? endDate;

  bool nameValid = true;
  bool startTimeValid = true;
  bool endTimeValid = true;
  bool startDateValid = true;

  TextEditingController nameController = TextEditingController();
  TextEditingController startTimeController = TextEditingController();
  TextEditingController endTimeController = TextEditingController();
  TextEditingController startDateController = TextEditingController();
  TextEditingController endDateController = TextEditingController();

  TextStyle titleStyle = TextStyle(fontSize: 16, fontWeight: FontWeight.bold);

  initState() {
    super.initState();
    setUpShiftInfo();
  }

  setUpShiftInfo() {
    this.nameController.text = widget.shift.name.toString();

    this.startTimeController.text =
        DateFormat("HH:mm:ss").format(widget.shift.startTime!);
    startTime = widget.shift.startTime;
    this.endTimeController.text =
        DateFormat("HH:mm:ss").format(widget.shift.endTime!);
    endTime = widget.shift.endTime;
    this.startDateController.text =
        DateFormat("dd-MM-yyyy").format(widget.shift.startDate!);
    startDate = widget.shift.startDate;
    if (widget.shift.endDate != null) {
      this.endDateController.text =
          DateFormat("dd-MM-yyyy").format(widget.shift.endDate!);
      endDate = widget.shift.endDate;
    } else {
      endDate = null;
      endDateController.text = "";
    }
    weekdays = [
      widget.shift.sunday,
      widget.shift.monday,
      widget.shift.tuesday,
      widget.shift.wednesday,
      widget.shift.thursday,
      widget.shift.friday,
      widget.shift.saturday
    ];
  }

  clearPage() {
    nameController.text = "";
    startTimeController.text = "";
    endTimeController.text = "";
    startDateController.text = "";
    endDateController.text = "";
    nameValid = false;
    startTimeValid = false;
    endTimeValid = false;
    startDateValid = false;
    startTime = null;
    endTime = null;
    startDate = null;
    endDate = null;
    weekdays = List.filled(7, false);
    setState(() {});
  }

  bool checkValidBeforeSubmit() {
    setState(() {});
    if (!(weekdays[0]! ||
        weekdays[1]! ||
        weekdays[2]! ||
        weekdays[3]! ||
        weekdays[4]! ||
        weekdays[5]! ||
        weekdays[6]!)) {
      return false;
    }
    _formKey.currentState!.validate();

    if (!(nameValid && startTimeValid && endTimeValid && startDateValid)) {
      return false;
    }
    return true;
  }

  Future<void> getSchedules() async {
    widget.schedules =
        await BkrmService().getSchedules(shiftId: widget.shift.shiftId);
    setState(() {});
  }

  List<Widget> buildColumn() {
    List<Widget> listSchedules = [];
    widget.schedules!.forEach((element) {
      listSchedules.add(InkWell(
        onTap: () async {
          showDialog(
              context: context,
              builder: (context) {
                return AlertDialog(
                  content: Container(
                    height: 50,
                    child: Center(child: CircularProgressIndicator()),
                  ),
                );
              });
          List<Map<String, dynamic>> attendanceListMap = await BkrmService()
              .getAttendancesFromDateToDate(DateTime.now(), DateTime.now(),scheduleId: element.scheduleId);
          Navigator.pop(context);
          AttendanceInfo? attendance;
          if (attendanceListMap.isNotEmpty) {
            attendance = attendanceListMap.first["attendance"];
          }

          Navigator.push(context, PageTransition(child: DetailSchedulePage(widget.shift, element,
              attendanceListMap.isEmpty ? null : attendance), type: pageTransitionType)).then((value) {
            if (value) {
              getSchedules().then((value) {
                setState(() {});
              });
            }
          });
        },
        child: Stack(
          children: [Card(
            child: Container(
              color: element.status != "enable" ? Colors.grey : Colors.white,
              padding: EdgeInsets.all(8.0),
              child: Column(
                children: [
                  Center(
                    child: Text(
                      element.name!,
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
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
                              child: Text("Ng??y b???t ?????u :" +
                                  DateFormat("dd-MM-yyyy")
                                      .format(element.scheduleStartDate!)))),
                      element.scheduleEndDate == null
                          ? Expanded(
                              flex: 1,
                              child: Container(
                                  alignment: Alignment.centerRight,
                                  child: Text("Ng??y k???t th??c: __/__/____")))
                          : Expanded(
                              flex: 1,
                              child: Container(
                                  alignment: Alignment.centerRight,
                                  child: Text("Ng??y k???t th??c:" +
                                      DateFormat("dd-MM-yyyy")
                                          .format(element.scheduleEndDate!)))),
                    ],
                  ),
                ],
              ),
            ),
          ),

            Positioned(child: IconButton(icon: Icon(Icons.close),onPressed: (){
              showDialog(context: context, builder: (context){
                return AlertDialog(
                  title: Text("B???n c?? ch???c mu???n x??a l???ch tr??nh n??y kh??ng?"),
                  actions: [
                    TextButton(onPressed: (){
                      Navigator.pop(context);
                    }, child: Text("H???y")),
                    TextButton(onPressed: ()async{
                      showDialog(context: context, builder: (context){
                        return AlertDialog(
                          content: Container(
                            height: 50,
                            child: Center(
                              child: CircularProgressIndicator(),
                            ),
                          ),
                        );
                      });
                      MsgInfoCode? returnStatus = await BkrmService().deleteSchedule(element.scheduleId!);
                      if(returnStatus==MsgInfoCode.actionSuccess){
                        await getSchedules();
                        setState(() {

                        });
                        Navigator.pop(context);
                        Navigator.pop(context);
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            title: Text("X??a l???ch tr??nh th??nh c??ng."),
                            actions: [TextButton(onPressed: (){
                              Navigator.pop(context);
                            }, child: Text("Ho??n th??nh"))],
                          );
                        });
                      }else{
                        showDialog(context: context, builder: (context){
                          return AlertDialog(
                            title: Text("X??a l???ch tr??nh th???t b???i."),
                            actions: [
                              TextButton(onPressed: (){Navigator.pop(context);}, child: Text("????ng"))
                            ],
                          );
                        });
                      }
                    }, child: Text("X??c nh???n")),
                  ],
                );
              });
            },),top: 0,right: 0,)]
        ),
      ));
    });
    listSchedules.add(InkWell(
      onTap: () {
        Navigator.push(context, PageTransition(child: AddNewSchedulePage(widget.shift), type: pageTransitionType)).then((value) async {
          if (value) {
            getSchedules();
          }
        });
      },
      child: Container(
        alignment: Alignment.center,
        height: 60,
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
            " Th??m l???ch tr??nh nh??n vi??n",
            style: TextStyle(
                fontSize: 16, fontWeight: FontWeight.w300, color: Colors.grey),
          )),
        ]),
      ),
    ));
    return listSchedules;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text("Chi ti???t ca"),
        ),
        body: WillPopScope(
          onWillPop: () async {
            Navigator.pop(context, needRefresh);
            return needRefresh;
          },
          child: SingleChildScrollView(
            child: Form(
              key: _formKey,
              child: Container(
                padding: EdgeInsets.all(8.0),
                child: Column(
                  children: [
                    SizedBox(
                      height: 30,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 1,
                            child: Text(
                              "T??n ca:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 2,
                            child: TextFormField(
                              enabled: isEdit,
                              controller: nameController,
                              autovalidateMode: AutovalidateMode.always,
                              validator: (name) {
                                if (name == null || name == "") {
                                  nameValid = false;
                                  return "* B???t bu???c";
                                }
                                nameValid = true;
                                return null;
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Th???i gian b???t ?????u:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                if (!isEdit) {
                                  return;
                                }
                                DatePicker.showTimePicker(context,
                                    showSecondsColumn: false,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  value = value.subtract(
                                      Duration(seconds: value.second));
                                  startTime = value;
                                  startTimeController.text =
                                      DateFormat("HH:mm:ss").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: startTimeController,
                                  autovalidateMode: AutovalidateMode.always,
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      startTimeValid = false;
                                      return "* B???t bu???c";
                                    }
                                    startTimeValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                if (!isEdit) {
                                  return;
                                }
                                startTime = null;
                                startTimeController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Th???i gian k???t th??c:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                if (!isEdit) {
                                  return null;
                                }
                                DatePicker.showTimePicker(context,
                                    showSecondsColumn: false,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  value = value.subtract(
                                      Duration(seconds: value.second));
                                  endTime = value;
                                  endTimeController.text =
                                      DateFormat("HH:mm:ss").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: endTimeController,
                                  autovalidateMode: AutovalidateMode.always,
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      endTimeValid = false;
                                      return "* B???t bu???c";
                                    }
                                    endTimeValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                if (!isEdit) {
                                  return;
                                }
                                endTime = null;
                                endTimeController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 20,
                    ),
                    Container(
                        alignment: Alignment.centerLeft,
                        child: Column(children: [
                          Text(
                            "Th??? trong tu???n: ",
                            style: titleStyle,
                          ),
                          (weekdays[0]! ||
                                  weekdays[1]! ||
                                  weekdays[2]! ||
                                  weekdays[3]! ||
                                  weekdays[4]! ||
                                  weekdays[5]! ||
                                  weekdays[6]!)
                              ? Container()
                              : Text(
                                  " * Ch???n ??t nh???t 1 ng??y trong tu???n",
                                  style: TextStyle(color: Colors.red),
                                )
                        ])),
                    Column(children: [
                      WeekdaySelector(
                        onChanged: (int day) {
                          if (!isEdit) {
                            return;
                          }
                          setState(() {
                            weekdays[day % 7] = !weekdays[day % 7]!;
                          });
                        },
                        shortWeekdays: ["CN", "2", "3", "4", "5", "6", "7"],
                        values: weekdays,
                      ),
                    ]),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Ng??y b???t ?????u:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                if (!isEdit) {
                                  return;
                                }
                                DatePicker.showDatePicker(context,
                                    locale: LocaleType.vi,
                                    maxTime: endDate,
                                    onConfirm: (DateTime value) {
                                  startDate = value;
                                  startDateController.text =
                                      DateFormat("dd-MM-yyyy").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: startDateController,
                                  autovalidateMode: AutovalidateMode.always,
                                  decoration: InputDecoration(
                                      errorStyle: TextStyle(color: Colors.red)),
                                  validator: (time) {
                                    if (time == null || time == "") {
                                      startDateValid = false;
                                      return "* B???t bu???c";
                                    }
                                    startDateValid = true;
                                    return null;
                                  },
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                if (!isEdit) {
                                  return;
                                }
                                startDate = null;
                                startDateController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 10,
                    ),
                    Row(
                      children: [
                        Expanded(
                            flex: 2,
                            child: Text(
                              "Ng??y k???t th??c:",
                              style: titleStyle,
                            )),
                        Expanded(
                            flex: 4,
                            child: InkWell(
                              onTap: () {
                                if (!isEdit) {
                                  return;
                                }
                                DatePicker.showDatePicker(context,
                                    minTime: startDate,
                                    locale: LocaleType.vi,
                                    onConfirm: (DateTime value) {
                                  endDate = value;
                                  endDateController.text =
                                      DateFormat("dd-MM-yyyy").format(value);
                                  setState(() {});
                                });
                              },
                              child: IgnorePointer(
                                child: TextFormField(
                                  textAlign: TextAlign.center,
                                  controller: endDateController,
                                ),
                              ),
                            )),
                        Expanded(
                            flex: 1,
                            child: IconButton(
                              icon: Icon(Icons.close),
                              onPressed: () {
                                if (!isEdit) {
                                  return;
                                }
                                endDate = null;
                                endDateController.text = "";
                              },
                            )),
                      ],
                    ),
                    SizedBox(
                      height: 30,
                    ),
                    Container(
                      child: isEdit
                          ? Row(
                              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                              children: [
                                ElevatedButton(
                                    onPressed: () {
                                      isEdit = false;
                                      setState(() {
                                        clearPage();
                                        setUpShiftInfo();
                                      });
                                    },
                                    child: Container(
                                      padding: EdgeInsets.all(10.0),
                                      child: Text(
                                        "H???y",
                                        style: titleStyle,
                                      ),
                                    )),
                                ElevatedButton(
                                  onPressed: () async {
                                    if (!isEdit) {
                                      isEdit = true;
                                      setState(() {});
                                      return;
                                    }

                                    if (!checkValidBeforeSubmit()) {
                                      return;
                                    }
                                    showDialog(
                                        context: context,
                                        builder: (context) {
                                          return AlertDialog(
                                            content: Container(
                                              height: 50,
                                              child: Center(
                                                  child:
                                                      CircularProgressIndicator()),
                                            ),
                                          );
                                        });
                                    MsgInfoCode? returnCode =
                                        await BkrmService().editShift(
                                            shiftId: widget.shift.shiftId,
                                            name: nameController.value.text,
                                            startTime: startTime,
                                            endTime: endTime,
                                            monday: weekdays[1],
                                            tuesday: weekdays[2],
                                            wednesday: weekdays[3],
                                            thursday: weekdays[4],
                                            friday: weekdays[5],
                                            saturday: weekdays[6],
                                            sunday: weekdays[0],
                                            startDate: startDate,
                                            endDate: endDate);
                                    Navigator.pop(context);
                                    if (returnCode ==
                                        MsgInfoCode.actionSuccess) {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Ch???nh s???a ca l??m vi???c th??nh c??ng."),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                      setState(() {
                                                        needRefresh = true;
                                                        isEdit = false;
                                                      });
                                                    },
                                                    child: Text("Ho??n th??nh"))
                                              ],
                                            );
                                          });
                                    } else {
                                      showDialog(
                                          context: context,
                                          builder: (context) {
                                            return AlertDialog(
                                              title: Text(
                                                  "Ch???nh s???a ca l??m vi???c th???t b???i."),
                                              actions: [
                                                FlatButton(
                                                    onPressed: () {
                                                      Navigator.pop(context);
                                                    },
                                                    child: Text("????ng"))
                                              ],
                                            );
                                          });
                                    }
                                  },
                                  child: Container(
                                    color: Colors.blue,
                                    padding: EdgeInsets.all(10.0),
                                    child: Text(
                                      "X??c nh???n",
                                      style: titleStyle,
                                    ),
                                  ),
                                ),
                              ],
                            )
                          : ElevatedButton(
                              onPressed: () async {
                                if (!isEdit) {
                                  isEdit = true;
                                  setState(() {});
                                  return;
                                }

                                if (!checkValidBeforeSubmit()) {
                                  return;
                                }
                                showDialog(
                                    context: context,
                                    builder: (context) {
                                      return AlertDialog(
                                        content: Container(
                                          height: 50,
                                          child: Center(
                                              child:
                                                  CircularProgressIndicator()),
                                        ),
                                      );
                                    });
                                MsgInfoCode? returnCode = await BkrmService()
                                    .editShift(
                                        shiftId: widget.shift.shiftId,
                                        name: nameController.value.text,
                                        startTime: startTime,
                                        endTime: endTime,
                                        monday: weekdays[1],
                                        tuesday: weekdays[2],
                                        wednesday: weekdays[3],
                                        thursday: weekdays[4],
                                        friday: weekdays[5],
                                        saturday: weekdays[6],
                                        sunday: weekdays[0],
                                        startDate: startDate,
                                        endDate: endDate);
                                Navigator.pop(context);
                                if (returnCode == MsgInfoCode.actionSuccess) {
                                  showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text(
                                              "Ch???nh s???a ca l??m vi???c th??nh c??ng"),
                                          actions: [
                                            FlatButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                  setState(() {
                                                    needRefresh = true;
                                                    isEdit = false;
                                                  });
                                                },
                                                child: Text("????ng"))
                                          ],
                                        );
                                      });
                                } else {
                                  showDialog(
                                      context: context,
                                      builder: (context) {
                                        return AlertDialog(
                                          title: Text(
                                              "Ch???nh s???a ca l??m vi???c th???t b???i"),
                                          actions: [
                                            FlatButton(
                                                onPressed: () {
                                                  Navigator.pop(context);
                                                },
                                                child: Text("????ng"))
                                          ],
                                        );
                                      });
                                }
                              },
                              child: Container(
                                color: Colors.blue,
                                padding: EdgeInsets.all(10.0),
                                child: Text(
                                  "Ch???nh s???a",
                                  style: titleStyle,
                                ),
                              ),
                            ),
                    ),
                    SizedBox(
                      height: 30,
                    ),
                    Column(
                      children: [
                        Center(
                          child: Text(
                            "L???ch tr??nh l??m vi???c",
                            style: titleStyle,
                          ),
                        ),
                        SizedBox(
                          height: 20,
                        ),
                        Column(
                          children: buildColumn(),
                        )
                      ],
                    )
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
