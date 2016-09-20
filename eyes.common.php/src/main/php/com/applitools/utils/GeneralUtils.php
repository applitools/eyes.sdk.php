<?php
/**
 * General purpose utilities.
 */
class GeneralUtils
{

    const DATE_FORMAT_ISO8601 = "yyyy-MM-dd'T'HH:mm:ss'Z'";
    const DATE_FORMAT_RFC1123 = "E, dd MMM yyyy HH:mm:ss 'GMT'";

    private function _construnct()
    {
    }

    /**
     * @param inputStream The stream which content we would like to read.
     * @return The entire contents of the input stream as a string.
     * @throws java.io.IOException If there was a problem reading/writing
     * from/to the streams used during the operation.
     */

    public static function readToEnd(InputStream $inputStream)/* throws IOException */
    {
        ArgumentGuard::notNull(inputStream, "inputStream");

        //noinspection SpellCheckingInspection
        /*ByteArrayOutputStream baos = new ByteArrayOutputStream();
        byte[] buffer = new byte[1024];
        int length;
        while ((length = inputStream.read(buffer)) != -1) {
        baos.write(buffer, 0, length);
        }

        return new String(baos.toByteArray());*/
        echo "MOCK_MOCK"; //FIXME
        die();
    }

    /**
     * Formats date and time as represented by a calendar instance to an ISO
     * 8601 string.
     *
     * @param calendar The date and time which we would like to format.
     * @return An ISO8601 formatted string representing the input date and time.
     */
    public static function toISO8601DateTime(Calendar $calendar)
    {
        /* ArgumentGuard::notNull($calendar, "calendar");

         SimpleDateFormat $formatter = new SimpleDateFormat(DATE_FORMAT_ISO8601, Locale.US);

         // For the string to be formatted correctly you MUST also set
         // the time zone in the formatter! See:
         // http://www.coderanch.com/t/376467/java/java/Display-time-timezones
         $formatter->setTimeZone(calendar.getTimeZone());

         return formatter.format(calendar.getTime());*/
        echo "MOCK2_MOCK2";   //FIXME
        die();
    }

    /**
     * Formats date and time as represented by a calendar instance to an TFC
     * 1123 string.
     *
     * @param calendar The date and time which we would like to format.
     * @return An RFC 1123 formatted string representing the input date and
     * time.
     */
    public static function toRfc1123(Calendar $calendar)
    {
        /*ArgumentGuard.notNull(calendar, "calendar");

        SimpleDateFormat formatter =
        new SimpleDateFormat(DATE_FORMAT_RFC1123, Locale.US);

        // For the string to be formatted correctly you MUST also set
        // the time zone in the formatter! See:
        // http://www.coderanch.com/t/376467/java/java/Display-time-timezones
        formatter.setTimeZone(calendar.getTimeZone());
        return formatter.format(calendar.getTime());*/
        echo "MOCK2_MOCK2";   //FIXME
        die();
    }

    /**
     * Creates {@link java.util.Calendar} instance from an ISO 8601 formatted
     * string.
     *
     * @param dateTime An ISO 8601 formatted string.
     * @return A {@link java.util.Calendar} instance representing the given
     *          date and time.
     * @throws java.text.ParseException If {@code dateTime} is not in the ISO
     * 8601 format.
     */
    public static function fromISO8601DateTime($dateTime)
    {
        /* throws ParseException {
         ArgumentGuard.notNull(dateTime, "dateTime");

         SimpleDateFormat formatter =
         new SimpleDateFormat(DATE_FORMAT_ISO8601);

         Calendar cal = Calendar.getInstance();
         cal.setTime(formatter.parse(dateTime));
         return cal;*/
        echo "MOCK3_MOCK3";  //FIXME
        die();
    }

    /**
     * Sleeps the input amount of milliseconds.
     *
     * @param milliseconds The number of milliseconds to sleep.
     */
    public static function sleep($microseconds)
    {
        try {
            usleep($microseconds);
        } catch (InterruptedException $ex) {
            throw new RuntimeException("sleep interrupted", $ex);
        }
    }

    /**
     * @param format The date format parser.
     * @param date The date string in a format matching {@code format}.
     * @return The {@link java.util.Date} represented by the input string.
     */
    public static function getDate(DateFormat $format, $date)
    {
        /*  try {
              return format.parse(date);
          } catch (ParseException ex) {
              throw new RuntimeException(ex);
          }*/
        echo "MOCK4_MOCK4";  //FIXME
        die();
    }

    /**
     *
     * @param start The start time. (Milliseconds)
     * @param end The end time. (Milliseconds).
     * @return The elapsed time between the start and end times, rounded up
     * to a full second, in milliseconds.
     */
    public static function getFullSecondsElapsedTimeMillis($start, $end)
    {
        // return ((long) Math.ceil((end - start) / 1000.0)) * 1000;
    }

    /**
     * Creates a {@link String} from a file specified by {@code resource}.
     *
     * @param resource The resource path.
     * @return The resource's text.
     * @throws EyesException If there was a problem reading the resource.
     */
    public static function readTextFromResource($resource)
    {
        /* InputStre   am is = GeneralUtils.class.getClassLoader()
         .getResourceAsStream(resource);

         BufferedReader br = new BufferedReader(new InputStreamReader(is));
         StringBuilder sb = new StringBuilder();
         try {
         String line = br.readLine();
         while (line != null) {
         sb.append(line);
         sb.append(System.lineSeparator());
         line = br.readLine();
         }

         try {
         br.close();
         } catch (IOException e) {
         // Nothing to do.
         }
         } catch (IOException e) {
         try {
         br.close();
         } catch (IOException e2) {
         // Nothing to do.
         }
         throw new EyesException("Failed to read text from resource: ", e);
         }
         return sb.toString();*/
        echo "MOCK5_MOCK5"; //FIXME
        die();
    }
}
