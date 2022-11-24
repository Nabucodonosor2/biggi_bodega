-------------------- spu_parametro ---------------------------------
CREATE PROCEDURE [dbo].[spu_parametro](	@ve_iva					varchar(100) 		
										,@ve_retencion_bh		varchar(100) 	
										,@ve_sistema			varchar(100)		
										,@ve_version			varchar(100)		
										,@ve_dolar_com			varchar(100)	
										,@ve_nom_empresa		varchar(100)		
										,@ve_giro_empresa		varchar(100)	
										,@ve_rut_empresa		varchar(100)		
										,@ve_validez_ofer_cot	varchar(100)	
										,@ve_entrega_cot		varchar(100)		
										,@ve_garantia_cot		varchar(100)	
										,@ve_direccion_empresa	varchar(100)	
										,@ve_fono_empresa		varchar(100)	
										,@ve_fax_empresa		varchar(100)		
										,@ve_mail_empresa		varchar(100)	
										,@ve_ciudad_empresa		varchar(100)	
										,@ve_pais_empresa		varchar(100)	
										,@ve_gerente_venta		varchar(100)	
										,@ve_smtp				varchar(100)		
										,@ve_user_autent		varchar(100)		
										,@ve_pass_autent		varchar(100)		
										,@ve_f_precio_int		varchar(100)	
										,@ve_f_precio_pub		varchar(100)	
										,@ve_f_precio_int_bajo	varchar(100)	
										,@ve_f_precio_int_alto	varchar(100)	
										,@ve_f_precio_pub_bajo	varchar(100)	
										,@ve_f_precio_pub_alto	varchar(100)	
										,@ve_p_cierre_NV		varchar(100)		
										,@ve_aporte_aa			varchar(100)		
										,@ve_fecha_aa			varchar(100)		
										,@ve_visible_porc_aa	varchar(100)
										,@ve_aporte_gf			varchar(100)		
										,@ve_fecha_gf			varchar(100)		
										,@ve_visible_porc_gf	varchar(100)
										,@ve_aporte_gv			varchar(100)		
										,@ve_fecha_gv			varchar(100)		
										,@ve_visible_porc_gv	varchar(100)
										,@ve_web_empresa		varchar(100)						
										,@ve_porc_max_dscto		varchar(100)
										,@ve_max_cant_gd		varchar(100)
										,@ve_max_cant_fa		varchar(100)
										,@ACCESO_LIBRE_A_COT	varchar(100)
										,@ACCESO_LIBRE_A_NV		varchar(100)
										,@ve_max_cant_nc		varchar(100)
										,@ve_aporte_adm			varchar(100)		
										,@ve_fecha_adm			varchar(100)		
										,@ve_visible_porc_adm	varchar(100)
										,@ve_direccion_ftp		varchar(100)
										,@ve_usuario_ftp		varchar(100)
										,@ve_password_ftp		varchar(100)
										,@ve_banco				varchar(100)
										,@ve_cta_cte			varchar(100)
										,@ve_cod_usuario 		numeric)
AS
BEGIN

declare @TEMP table 
	(cod_parametro		numeric
	,valor				varchar(100)
	)
	
insert into @TEMP
select cod_parametro
		,VALOR
from PARAMETRO

declare
	@vl_cod_log_cambio		numeric
	,@vl_cod_parametro_porc	numeric
	,@vl_cod_str			varchar(100)
		
exec sp_log_cambio 'PARAMETRO', '0', @ve_cod_usuario, 'U'
set @vl_cod_log_cambio = @@IDENTITY
	
update parametro set valor =  @ve_iva 				where cod_parametro = 1;
update parametro set valor =  @ve_retencion_bh 		where cod_parametro = 2;  	
update parametro set valor =  @ve_sistema			where cod_parametro = 3;
update parametro set valor =  @ve_version			where cod_parametro = 4;
update parametro set valor =  @ve_dolar_com			where cod_parametro = 5;
update parametro set valor =  @ve_nom_empresa		where cod_parametro = 6;
update parametro set valor =  @ve_validez_ofer_cot	where cod_parametro = 7;
update parametro set valor =  @ve_entrega_cot		where cod_parametro = 8;
update parametro set valor =  @ve_garantia_cot		where cod_parametro = 9;
update parametro set valor =  @ve_direccion_empresa	where cod_parametro = 10;
update parametro set valor =  @ve_fono_empresa		where cod_parametro = 11;
update parametro set valor =  @ve_fax_empresa		where cod_parametro = 12;
update parametro set valor =  @ve_mail_empresa		where cod_parametro = 13;
update parametro set valor =  @ve_ciudad_empresa	where cod_parametro = 14;
update parametro set valor =  @ve_pais_empresa		where cod_parametro = 15;
update parametro set valor =  @ve_gerente_venta		where cod_parametro = 16;
update parametro set valor =  @ve_smtp				where cod_parametro = 17;
update parametro set valor =  @ve_user_autent		where cod_parametro = 18;
update parametro set valor =  @ve_pass_autent		where cod_parametro = 19;
update parametro set valor =  @ve_rut_empresa		where cod_parametro = 20;
update parametro set valor =  @ve_giro_empresa		where cod_parametro = 21;
update parametro set valor =  @ve_f_precio_int		where cod_parametro = 22;
update parametro set valor =  @ve_f_precio_pub		where cod_parametro = 23;
update parametro set valor =  @ve_p_cierre_NV		where cod_parametro = 24;
update parametro set valor =  @ve_web_empresa		where cod_parametro = 25;
update parametro set valor =  @ve_porc_max_dscto	where cod_parametro = 26;
update parametro set valor =  @ve_max_cant_gd		where cod_parametro = 28;
update parametro set valor =  @ve_max_cant_fa		where cod_parametro = 29;
update parametro set valor =  @ve_f_precio_int_bajo	where cod_parametro = 34;
update parametro set valor =  @ve_f_precio_int_alto	where cod_parametro = 35;
update parametro set valor =  @ve_f_precio_pub_bajo	where cod_parametro = 36;
update parametro set valor =  @ve_f_precio_pub_alto	where cod_parametro = 37;
update parametro set valor =  @ACCESO_LIBRE_A_COT	where cod_parametro = 38;
update parametro set valor =  @ACCESO_LIBRE_A_NV	where cod_parametro = 39;
update parametro set valor =  @ve_max_cant_nc		where cod_parametro = 40;
update parametro set valor =  @ve_direccion_ftp		where cod_parametro = 42;
update parametro set valor =  @ve_usuario_ftp		where cod_parametro = 43;
update parametro set valor =  @ve_password_ftp		where cod_parametro = 44;
update parametro set valor =  @ve_banco				where cod_parametro = 61;
update parametro set valor =  @ve_cta_cte			where cod_parametro = 62;

insert into DETALLE_CAMBIO
select @vl_cod_log_cambio
		,p.NOM_PARAMETRO
		,t.valor
		,p.VALOR
from PARAMETRO p, @TEMP t
where p.COD_PARAMETRO = t.cod_parametro
and p.VALOR <> t.valor

if (@ve_visible_porc_aa = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_aa, dbo.to_date(@ve_fecha_aa), 'AA');
		
		set @vl_cod_parametro_porc = @@IDENTITY
		set @vl_cod_str = convert(varchar, @vl_cod_parametro_porc)
		exec sp_detalle_cambio @vl_cod_log_cambio, 'parametro_porc AA', '', @vl_cod_str
	end

if (@ve_visible_porc_gf = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_gf, dbo.to_date(@ve_fecha_gf), 'GF');
		
		set @vl_cod_parametro_porc = @@IDENTITY
		set @vl_cod_str = convert(varchar, @vl_cod_parametro_porc)
		exec sp_detalle_cambio @vl_cod_log_cambio, 'parametro_porc GF', '', @vl_cod_str
	end
if (@ve_visible_porc_gv = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_gv, dbo.to_date(@ve_fecha_gv), 'GV');
		
		set @vl_cod_parametro_porc = @@IDENTITY
		set @vl_cod_str = convert(varchar, @vl_cod_parametro_porc)
		exec sp_detalle_cambio @vl_cod_log_cambio, 'parametro_porc GV', '', @vl_cod_str
	end
if (@ve_visible_porc_adm = 'S') 
	begin	
		insert into parametro_porc 
						(porc_parametro, fecha_inicio_vigencia, tipo_parametro)
		values			(@ve_aporte_adm, dbo.to_date(@ve_fecha_adm), 'ADM');
		
		set @vl_cod_parametro_porc = @@IDENTITY
		set @vl_cod_str = convert(varchar, @vl_cod_parametro_porc)
		exec sp_detalle_cambio @vl_cod_log_cambio, 'parametro_porc ADM', '', @vl_cod_str
	end

END
go
