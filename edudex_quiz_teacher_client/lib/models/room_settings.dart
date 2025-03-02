class RoomSettings {
  final String startIp; // IP bắt đầu
  final String endIp; // IP kết thúc
  final List<String> blockedIps;
  final int maxComputers;

  RoomSettings({
    required this.startIp,
    required this.endIp,
    required this.blockedIps,
    required this.maxComputers,
  });

  bool isIpAllowed(String ip) {
    if (blockedIps.contains(ip)) return false;
    return _isIpInRange(ip, startIp, endIp);
  }

  bool _isIpInRange(String ip, String start, String end) {
    final ipNum = _ipToNumber(ip);
    final startNum = _ipToNumber(start);
    final endNum = _ipToNumber(end);
    return ipNum >= startNum && ipNum <= endNum;
  }

  int _ipToNumber(String ip) {
    final parts = ip.split('.');
    var result = 0;
    for (var part in parts) {
      result = result << 8 | int.parse(part);
    }
    return result;
  }
}
