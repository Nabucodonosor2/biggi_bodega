-------------------- spi_mantencion_sw ---------------------------------	
alter  PROCEDURE spi_mantencion_sw(@ve_fecha_inicio				datetime
									,@ve_fecha_termino			datetime
									,@ve_cod_usuario			numeric
									,@ve_cod_estado_solucion_sw	numeric)
AS
BEGIN
	declare @TEMPO TABLE    
	   (COD_MANTENCION_SW			numeric
		,FECHA_MANTENCION_SW		varchar(20)
		,NOM_USUARIO				varchar(100)
		,REFERENCIA		varchar(100)
		,MINUTOS					numeric
		)

	insert into @TEMPO
	   (COD_MANTENCION_SW
		,FECHA_MANTENCION_SW
		,NOM_USUARIO
		,REFERENCIA
		,MINUTOS
		)
	select M.COD_MANTENCION_SW
			,convert(varchar, M.FECHA_MANTENCION_SW, 103) FECHA_MANTENCION_SW
			,U.NOM_USUARIO
			,REFERENCIA
			,0
	from MANTENCION_SW M, USUARIO U, ESTADO_SOLUCION_SW E
	where M.FECHA_MANTENCION_SW between @ve_fecha_inicio and @ve_fecha_termino
	  and (@ve_cod_usuario=0 or M.COD_USUARIO_SOLICITA = @ve_cod_usuario)
	  and E.COD_ESTADO_SOLUCION_SW = dbo.f_mant_estado_solucion(M.COD_MANTENCION_SW)
	  and E.COD_ESTADO_SOLUCION_SW = @ve_cod_estado_solucion_sw
	  and U.COD_USUARIO = M.COD_USUARIO_SOLICITA
	order by M.COD_MANTENCION_SW

	DECLARE C_TEMPO CURSOR FOR  
	SELECT COD_MANTENCION_SW from @TEMPO

	declare
		@cod_mantencion_sw		numeric,
		@suma_minutos			numeric,
		@total_minutos			numeric

	set @total_minutos = 0
	OPEN C_TEMPO
	FETCH C_TEMPO INTO @cod_mantencion_sw
	WHILE @@FETCH_STATUS = 0 BEGIN	
		select @suma_minutos = isnull(sum(minutos), 0)
		from solucion_sw
		where cod_mantencion_sw = @cod_mantencion_sw
	
		update @TEMPO
		set MINUTOS = @suma_minutos
		where cod_mantencion_sw = @cod_mantencion_sw

		set @total_minutos = @total_minutos + @suma_minutos

		FETCH C_TEMPO INTO @cod_mantencion_sw
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	select 'INF_MANTENCION_SW' INF_MANTENCION_SW
			,COD_MANTENCION_SW
			,FECHA_MANTENCION_SW
			,NOM_USUARIO
			,REFERENCIA
			,convert(varchar, floor(MINUTOS / 60)) + ':' + right('00' + convert(varchar, MINUTOS % 60), 2) HORAS
			,convert(varchar, floor(@total_minutos / 60)) + ':' + right('00' + convert(varchar, @total_minutos % 60), 2) HORAS_TOTAL
	from @TEMPO 
	order by COD_MANTENCION_SW
END
