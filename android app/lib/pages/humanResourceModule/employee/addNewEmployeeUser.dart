import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_datetime_picker/flutter_datetime_picker.dart';
import 'package:intl/intl.dart';

class AddNewEmployeeUserPage extends StatefulWidget {
  @override
  _AddNewEmployeeUserPageState createState() => _AddNewEmployeeUserPageState();
}

class _AddNewEmployeeUserPageState extends State<AddNewEmployeeUserPage> {
  final _formKey = GlobalKey<FormState>();

  String? genderValue;
  DateTime? dateOfBirth;

  bool needRefresh= false;

  TextEditingController fullNameController = TextEditingController();
  TextEditingController userNameController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController confirmPasswordController = TextEditingController();
  TextEditingController dateOfBirthController = TextEditingController();
  TextEditingController phoneController = TextEditingController();
  TextEditingController emailController = TextEditingController();

  bool fullNameValid = false;
  bool userNameValid = false;
  bool passwordValid = false;
  bool confirmPasswordValid = false;
  bool permissionValid = false;
  bool emailValid = true;

  bool? selling = false;
  bool? managing = false;
  bool? purchasing = false;
  bool? reporting = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Thêm nhân viên mới"),
      ),
      body: WillPopScope(
        onWillPop: ()async{
          Navigator.pop(context,needRefresh);
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
                  Center(
                    child: Text(
                      "Tạo nhân viên mới",
                      style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold),
                    ),
                  ),
                  SizedBox(
                    height: 30,
                  ),
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
                          controller: fullNameController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (name){
                            if(name==""||name==null){
                              fullNameValid=false;
                              return " *Bắt buộc";
                            }
                            fullNameValid=true;
                            return null;
                          },
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
                            "Tên đăng nhập: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          maxLength: 20,
                          controller: userNameController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (userName) {
                            if (userName == null || userName == "") {
                              userNameValid = false;
                              return " *Bắt buộc";
                            } else {
                              userNameValid = true;
                              return null;
                            }
                          },
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
                            "Mật khẩu: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          obscureText: true,
                          controller: passwordController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (password) {
                            if (password == null || password == "") {
                              passwordValid = false;
                              return " *Bắt buộc";
                            } else {
                              passwordValid = true;
                              return null;
                            }
                          },
                        ),
                      )
                    ],
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Nhập lại mật khẩu: ",
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          obscureText: true,
                          controller: confirmPasswordController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (password) {
                            if (password == null || password == "") {
                              confirmPasswordValid = false;
                              return " *Bắt buộc";
                            } else {
                              if(password!=passwordController.value.text){
                                confirmPasswordValid = false;
                                return " * Không giống mật khẩu";
                              }
                              confirmPasswordValid = true;
                              return null;
                            }
                          },
                        ),
                      )
                    ],
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
                          autovalidateMode: AutovalidateMode.always,
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
                          keyboardType: TextInputType.phone,
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
                        style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                      ),
                      Radio(
                          value: "male",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nam",
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Radio(
                          value: "female",
                          groupValue: genderValue,
                          onChanged: (dynamic value) {
                            genderValue = value;
                            setState(() {});
                          }),
                      Text(
                        "Nữ",
                        style:
                            TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
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
                            DatePicker.showDatePicker(context,
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
                  Text(
                    "Các quyền cho phép: ",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Checkbox(
                          value: selling,
                          onChanged: (value) {
                            selling = value;
                            setState(() {});
                          }),
                      Text("Bán hàng"),
                      Checkbox(
                          value: purchasing,
                          onChanged: (value) {
                            purchasing = value;
                            setState(() {});
                          }),
                      Text("Kho hàng"),
                    ],
                  ),
                  SizedBox(
                    height: 10,
                  ),
                  Row(
                    children: [
                      Checkbox(
                          value: managing,
                          onChanged: (value) {
                            managing = value;
                            setState(() {});
                          }),
                      Text("Nhân sự"),
                      Checkbox(
                          value: reporting,
                          onChanged: (value) {
                            reporting = value;
                            setState(() {});
                          }),
                      Text("Quản lý và xem báo cáo"),
                    ],
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Center(
                      child: RaisedButton(
                    padding: EdgeInsets.all(16.0),
                    color: Colors.blue,
                    onPressed: () async {
                      if (selling! || reporting! || purchasing! || managing!) {
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
                      if (passwordValid && userNameValid && permissionValid||fullNameValid) {
                        showDialog(
                            context: context,
                            builder: (context) {
                              return AlertDialog(
                                content: Container(
                                  height: 60,
                                  child: Center(
                                    child: CircularProgressIndicator(),
                                  ),
                                ),
                              );
                            });
                        MsgInfoCode? returnCode = await BkrmService()
                            .createEmployeeUser(
                                name: fullNameController.value.text,
                                username: userNameController.value.text,
                                password: passwordController.value.text,
                                dateOfBirth: dateOfBirth,
                                gender: genderValue == "" ? null : genderValue,
                                email: emailController.value.text == ""
                                    ? null
                                    : emailController.value.text,
                                phoneNumber: phoneController.value.text == ""
                                    ? null
                                    : phoneController.value.text,
                                selling: selling,
                                editing: managing,
                                purchasing: purchasing,
                                reporting: reporting);
                        Navigator.pop(context);
                        if (returnCode == MsgInfoCode.signUpSuccess) {
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text("Tạo tài khoản thành công."),
                                  actions: [
                                    FlatButton(
                                        onPressed: () {
                                          needRefresh=true;
                                          Navigator.pop(context);
                                          Navigator.pop(context,needRefresh);
                                        },
                                        child: Text("Hoàn thành"))
                                  ],
                                );
                              });
                          return;
                        } else {
                          if (returnCode ==
                              MsgInfoCode.usernameAlreadyBeenTaken) {
                            showDialog(
                                context: context,
                                builder: (context) {
                                  return AlertDialog(
                                    title: Text(
                                        "Tên đăng nhập đã tồn tại."),
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
                          showDialog(
                              context: context,
                              builder: (context) {
                                return AlertDialog(
                                  title: Text("Tạo tài khoản thất bại."),
                                  actions: [
                                    FlatButton(
                                        onPressed: () {
                                          Navigator.pop(context);
                                        },
                                        child: Text("Đóng"))
                                  ],
                                );
                              });
                        }
                      }
                    },
                    child: Text(
                      "Thêm nhân viên",
                      style: TextStyle(color: Colors.white, fontSize: 20),
                    ),
                  ))
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
