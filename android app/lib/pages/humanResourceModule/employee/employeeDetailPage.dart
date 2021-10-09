import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/pages/humanResourceModule//employee/changePasswordPage.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class EmployeeDetailPage extends StatefulWidget {
  EmployeeInfo employee;

  EmployeeDetailPage(this.employee);

  @override
  _EmployeeDetailPageState createState() => _EmployeeDetailPageState();
}

class _EmployeeDetailPageState extends State<EmployeeDetailPage> {
  final _formKey = GlobalKey<FormState>();

  String? genderValue;
  DateTime? dateOfBirth;

  bool enableEdit = false;

  bool needRefresh = false;

  TextEditingController fullNameController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();
  TextEditingController phoneController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController usernameController = TextEditingController();
  bool fullNameValid = false;
  bool permissionValid = false;
  bool emailValid = true;

  bool selling = false;
  bool managing = false;
  bool purchasing = false;
  bool reporting = false;

  @override
  void initState() {
    super.initState();
    setInfoFromEmployee();
  }

  setInfoFromEmployee() {
    debugPrint("status :"+widget.employee.status.toString());
    usernameController.text=widget.employee.userName;
    fullNameController.text = widget.employee.name;
    genderValue = widget.employee.gender;
    if (widget.employee.dateOfBirth != null) {
      dateOfBirthController.text =
          DateFormat("dd-MM-yyyy").format(widget.employee.dateOfBirth!);
    }
    dateOfBirth = widget.employee.dateOfBirth;
    if (widget.employee.phone != null) {
      phoneController.text = widget.employee.phone!;
    }
    if (widget.employee.email != null) {
      emailController.text = widget.employee.email!;
    }
    if(widget.employee.gender!=null){
      genderValue=widget.employee.gender;
    }
    debugPrint(widget.employee.gender);
    if (widget.employee.roles != null) {
      selling = widget.employee.roles!.contains("selling");
      managing = widget.employee.roles!.contains("managing");
      reporting = widget.employee.roles!.contains("reporting");
      purchasing = widget.employee.roles!.contains("purchasing");
    }
  }

  clearPage() {
    emailController.clear();
    phoneController.clear();
    dateOfBirthController.clear();
    fullNameController.clear();

    genderValue=null;

    selling = false;
    managing = false;
    purchasing = false;
    reporting = false;
  }

  remoteSetState(){
    setState(() {

    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Thông tin nhân viên"),
      ),
      body: WillPopScope(
        onWillPop: () async {
          Navigator.pop(context, needRefresh);
          return needRefresh;
        },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tên đăng nhập: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: false,
                          controller: usernameController,
                          validator: (name) {
                            if (name == "" || name == null) {
                              fullNameValid = false;
                              return " *Bắt buộc";
                            }
                            fullNameValid = false;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(height: 10,),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Họ tên: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: enableEdit,
                          controller: fullNameController,
                          validator: (name) {
                            if (name == "" || name == null) {
                              fullNameValid = false;
                              return " *Bắt buộc";
                            }
                            fullNameValid = false;
                            return null;
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Email: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: enableEdit,
                          autovalidateMode: AutovalidateMode.always,
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                          validator: (email) {
                            if (email == "" || email == null) {
                              emailValid = true;
                              return null;
                            }
                            if (RegExp(
                                    r"^[a-zA-Z0-9.a-zA-Z0-9.!#$%&'*+-/=?^_`{|}~]+@[a-zA-Z0-9]+\.[a-zA-Z]+")
                                .hasMatch(email)) {
                              emailValid = true;
                              return null;
                            } else {
                              emailValid = false;
                              return "Email không hợp lệ";
                            }
                          },
                          controller: emailController,
                        ),
                      )
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Số điện thoại: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(
                                  borderSide: BorderSide(color: Colors.blue))),
                          keyboardType: TextInputType.phone,
                          enabled: enableEdit,
                          controller: phoneController,
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Text(
                        "Giới tính: ",
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w500),
                      ),
                      Radio(
                        activeColor: enableEdit?Colors.blue:Colors.grey,
                        toggleable: enableEdit,
                          value: "male",
                          groupValue: genderValue,
                        onChanged: (String? gender){
                          if(!enableEdit) return;
                          genderValue=gender??"male";
                          setState(() {

                          });
                        },
                        ),
                      Text(
                        "Nam",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Radio(
                          activeColor: enableEdit?Colors.blue:Colors.grey,
                        toggleable: enableEdit,
                          value: "female",
                          groupValue: genderValue,
                          onChanged: (String? gender){
                            if(!enableEdit) return;
                            genderValue=gender??"female";
                            setState(() {

                            });
                          }),
                      Text(
                        "Nữ",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Ngày sinh: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: InkWell(
                          onTap: () {
                            if (!enableEdit) {
                              return;
                            }
                            DatePicker.showDatePicker(
                                context,
                                locale: LocaleType.vi
                                ,
                                currentTime: DateTime.now(),
                                maxTime: DateTime.now(), onConfirm: (date) {
                              dateOfBirth = date;
                              dateOfBirthController.text =
                                  DateFormat("dd-MM-yyyy").format(date);
                              setState(() {});
                            });
                          },
                          child: IgnorePointer(
                            child: TextFormField(
                              enabled: enableEdit,
                              decoration: InputDecoration(
                                  enabledBorder: UnderlineInputBorder(
                                      borderSide:
                                          BorderSide(color: Colors.blue))),
                              readOnly: true,
                              controller: dateOfBirthController,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  SizedBox(
                    height: 20,
                  ),
                  widget.employee.roles != null
                      ? Column(
                          children: [
                            Text(
                              "Các quyền cho phép: ",
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w500),
                            ),
                            SizedBox(
                              height: 10,
                            ),
                            Row(
                              children: [
                                Expanded(
                                  flex: 1,
                                  child: Checkbox(
                                      value: selling,
                                      onChanged: enableEdit
                                          ? (value) {
                                              if (!enableEdit) {
                                                return;
                                              }
                                              selling = value??false;
                                              setState(() {});
                                            }
                                          : null),
                                ),
                                Expanded(flex: 2, child: Text("Bán hàng")),
                                Expanded(
                                  flex: 1,
                                  child: Checkbox(
                                      value: purchasing,
                                      onChanged: enableEdit
                                          ? (value) {
                                              if (!enableEdit) {
                                                return;
                                              }
                                              purchasing = value??false;
                                              setState(() {});
                                            }
                                          : null),
                                ),
                                Expanded(flex: 2, child: Text("Kho hàng")),
                              ],
                            ),
                            SizedBox(
                              height: 10,
                            ),
                            Row(
                              children: [
                                Expanded(
                                  flex: 1,
                                  child: Checkbox(
                                      value: managing,
                                      onChanged: enableEdit
                                          ? (value) {
                                              if (!enableEdit) {
                                                return;
                                              }
                                              managing = value??false;
                                              setState(() {});
                                            }
                                          : null),
                                ),
                                Expanded(flex: 2, child: Text("Nhân sự")),
                                Expanded(
                                  flex: 1,
                                  child: Center(
                                    child: Checkbox(
                                        value: reporting,
                                        onChanged: enableEdit
                                            ? (value) {
                                                if (!enableEdit) {
                                                  return;
                                                }
                                                reporting = value??false;
                                                setState(() {});
                                              }
                                            : null),
                                  ),
                                ),
                                Expanded(
                                    flex: 2,
                                    child: Text("Quản lý và xem báo cáo")),
                              ],
                            ),
                            SizedBox(
                              height: 30,
                            ),
                          ],
                        )
                      : Container(),
                  widget.employee.roles != null
                      ? Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: enableEdit
                              ? <Widget>[
                                  Container(
                                    padding: EdgeInsets.all(10.0),
                                    child: ElevatedButton(
                                        onPressed: () {
                                          if (enableEdit) {
                                            enableEdit = false;
                                            clearPage();
                                            setInfoFromEmployee();
                                            setState(() {});
                                          }
                                        },
                                        child: Container(
                                            padding: EdgeInsets.all(10.0),
                                            child: Text(
                                              "Hủy",
                                              style: TextStyle(
                                                  color: Colors.white,
                                                  fontSize: 20),
                                            ))),
                                  ),
                                  Container(
                                    padding: EdgeInsets.all(10.0),
                                    child: ElevatedButton(
                                      style: ElevatedButton.styleFrom(primary: Colors.blue),
                                      onPressed: () async {
                                        if (!enableEdit) {
                                          enableEdit = true;
                                          setState(() {});
                                          return;
                                        }
                                        if (selling ||
                                            reporting ||
                                            purchasing ||
                                            managing) {
                                          permissionValid = true;
                                        } else {
                                          permissionValid = false;
                                        }
                                        if (!permissionValid) {
                                          showDialog(
                                              context: context,
                                              builder: (context) {
                                                return AlertDialog(
                                                  title: Text(
                                                      "Tài khoản phải được cấp ít nhất 1 quyền"),
                                                  actions: [
                                                    FlatButton(
                                                        onPressed: () {
                                                          Navigator.pop(context);
                                                        },
                                                        child: Text("Đóng"))
                                                  ],
                                                );
                                              });
                                          return;
                                        }
                                        _formKey.currentState!.validate();
                                        if (permissionValid || fullNameValid||emailValid) {
                                          showDialog(
                                              context: context,
                                              builder: (context) {
                                                return AlertDialog(
                                                  content: Container(
                                                    height: 60,
                                                    child: Center(
                                                      child:
                                                          CircularProgressIndicator(),
                                                    ),
                                                  ),
                                                );
                                              });
                                          MsgInfoCode? returnCode =
                                              await BkrmService()
                                                  .editEmployeeInfo(
                                                      id:
                                                          widget.employee.userId,
                                                      name: fullNameController.value.text,
                                                      gender: genderValue,
                                                      email: emailController.value.text,
                                                      dateOfBirth: dateOfBirth,
                                                      phoneNumber: phoneController.value.text,
                                                      selling: selling,
                                                      managing: managing,
                                                      purchasing: purchasing,
                                                      reporting: reporting);
                                          Navigator.pop(context);
                                          if (returnCode ==
                                              MsgInfoCode.actionSuccess) {
                                            widget.employee.email=emailController.value.text;
                                            widget.employee.name=fullNameController.value.text;
                                            widget.employee.phone=phoneController.value.text;
                                            widget.employee.gender=genderValue;
                                            widget.employee.dateOfBirth=dateOfBirth;
                                            List<String> roles =[];
                                            if(selling){
                                              roles.add("selling");
                                            }
                                            if(purchasing){
                                              roles.add("purchasing");
                                            }
                                            if(managing){
                                              roles.add("managing");
                                            }
                                            if(reporting){
                                              roles.add("reporting");
                                            }
                                            widget.employee.roles=roles;
                                            setState(() {

                                            });
                                            showDialog(
                                                context: context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text(
                                                        "Chỉnh sửa thành công"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                            needRefresh=true;
                                                            enableEdit=false;
                                                            this.remoteSetState();
                                                          },
                                                          child: Text("Đóng"))
                                                    ],
                                                  );
                                                });
                                            return;
                                          } else {
                                            showDialog(
                                                context: context,
                                                builder: (context) {
                                                  return AlertDialog(
                                                    title: Text(
                                                        "Chỉnh sửa thất bại"),
                                                    actions: [
                                                      FlatButton(
                                                          onPressed: () {
                                                            Navigator.pop(
                                                                context);
                                                          },
                                                          child: Text("Đóng"))
                                                    ],
                                                  );
                                                });
                                          }
                                        }
                                      },
                                      child: Container(
                                        padding: EdgeInsets.all(10.0),
                                        child: Text(
                                          enableEdit
                                              ? "Xác nhận"
                                              : "Chỉnh sửa",
                                          style: TextStyle(
                                              color: Colors.white, fontSize: 20),
                                        ),
                                      ),
                                    ),
                                  ),
                                ]
                              : <Widget>[
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(primary: Colors.blue),
                              onPressed: () async {
                                if (!enableEdit) {
                                  enableEdit = true;
                                  setState(() {});
                                  return;
                                }
                              },
                              child: Container(
                                padding: EdgeInsets.all(10.0),
                                child: Text(
                                  enableEdit
                                      ? "Xác nhận"
                                      : "Chỉnh sửa",
                                  style: TextStyle(
                                      color: Colors.white, fontSize: 20),
                                ),
                              ),
                            ),
                                ])
                      : Container(),
                  SizedBox(height: 30,),
                  BkrmService().currentUser!.userId==BkrmService().currentUser!.storeOwnerId?Center(child:
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(primary: Colors.blue),
                    onPressed: (){
                      Navigator.push(context,PageTransition(child: ChangePasswordPage(widget.employee), type: pageTransitionType));
                    },
                    child: Container(
                      padding: EdgeInsets.all(10.0),
                      child: Text("Đổi mật khẩu",style: TextStyle(fontSize: 20,color: Colors.white),),
                    ),
                  ),
                    ):Container(),
                  SizedBox(height: 30,),
                  BkrmService().currentUser!.userId==BkrmService().currentUser!.storeOwnerId?Center(child:
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(primary: Colors.redAccent),
                    onPressed: ()async{
                      showDialog(
                          context: context,
                          builder: (context) {
                            return AlertDialog(
                              content: Container(
                                height: 60,
                                child: Center(
                                  child:
                                  CircularProgressIndicator(),
                                ),
                              ),
                            );
                          });
                      bool statusChangeTo = !(widget.employee.status=="enable");
                        MsgInfoCode? returnCode = await BkrmService().changeStatusAccount(widget.employee.userId, statusChangeTo);

                        Navigator.pop(context);
                        if (returnCode ==
                            MsgInfoCode.actionSuccess) {
                          this.setState(() {
                            widget.employee.status=statusChangeTo?"enable":"disable";
                          });
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text(
                                      "Chỉnh sửa thành công"),
                                  actions: [
                                    FlatButton(
                                        onPressed: () {
                                          Navigator.pop(
                                              context);
                                          needRefresh=true;
                                          enableEdit=false;
                                          this.remoteSetState();
                                        },
                                        child: Text("Đóng"))
                                  ],
                                );
                              });
                          return;
                        } else {
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text(
                                      "Chỉnh sửa thất bại"),
                                  actions: [
                                    FlatButton(
                                        onPressed: () {
                                          Navigator.pop(
                                              context);
                                        },
                                        child: Text("Đóng"))
                                  ],
                                );
                              });
                        }
                    },
                    child: Container(
                      padding: EdgeInsets.all(10.0),
                      child: Text(widget.employee.status=="enable"?"Vô hiệu hóa":"Kích hoạt",style: TextStyle(fontSize: 20,color: Colors.white),),
                    ),
                  ),
                  ):Container(),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
